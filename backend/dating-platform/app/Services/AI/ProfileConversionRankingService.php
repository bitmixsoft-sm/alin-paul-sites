<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Message;
use App\Order;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Ranks female profiles by how much paid-package revenue can be attributed to them - the
 * client's request was specifically to find "the profile that brought in the most subscriptions
 * by convincing clients", to use as a style-learning source (see AdminStyleLearningController).
 *
 * Attribution model: for each accepted order, find the female profile the buyer was messaging
 * most recently in the lookback window right before they paid, and credit that order's revenue
 * to her. This is a heuristic (a buyer may have been talking to more than one profile), not a
 * guaranteed causal link, but it's the same kind of "last touch before conversion" attribution
 * commonly used for this sort of thing, and needs no new tracking/schema - it works off data
 * (Message, Order) the app already records.
 */
final class ProfileConversionRankingService
{
    /**
     * @return array<int, array{user_id: int, name: string, conversions: int, revenue: float}>
     */
    public function rank(int $lookbackDays = 7, int $orderLimit = 2000): array
    {
        $orders = Order::query()
            ->where('status', 'Accepted')
            ->latest('created_at')
            ->limit($orderLimit)
            ->get(['user_id', 'price', 'created_at']);

        if ($orders->isEmpty()) {
            return [];
        }

        $femaleIds = User::where('gender', 'female')->pluck('id')->flip();

        $totals = [];

        foreach ($orders as $order) {
            $femaleId = $this->lastFemaleContactBefore((int) $order->user_id, $order->created_at, $lookbackDays, $femaleIds);

            if ($femaleId === null) {
                continue;
            }

            $totals[$femaleId]['conversions'] = ($totals[$femaleId]['conversions'] ?? 0) + 1;
            $totals[$femaleId]['revenue'] = ($totals[$femaleId]['revenue'] ?? 0.0) + (float) $order->price;
        }

        if (empty($totals)) {
            return [];
        }

        $names = User::whereIn('id', array_keys($totals))->get(['id', 'firstname', 'lastname'])->keyBy('id');

        $result = [];

        foreach ($totals as $userId => $data) {
            if (! isset($names[$userId])) {
                continue;
            }

            $result[] = [
                'user_id' => $userId,
                'name' => $names[$userId]->name(),
                'conversions' => $data['conversions'],
                'revenue' => round($data['revenue'], 2),
            ];
        }

        usort($result, fn (array $a, array $b): int => $b['revenue'] <=> $a['revenue']);

        return $result;
    }

    private function lastFemaleContactBefore(int $buyerId, Carbon $before, int $lookbackDays, Collection $femaleIds): ?int
    {
        $since = $before->copy()->subDays($lookbackDays);

        $messages = Message::query()
            ->where(function ($query) use ($buyerId) {
                $query->where('from_user', $buyerId)->orWhere('to_user', $buyerId);
            })
            ->whereBetween('created_at', [$since, $before])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['from_user', 'to_user']);

        foreach ($messages as $message) {
            $otherId = (int) $message->from_user === $buyerId ? (int) $message->to_user : (int) $message->from_user;

            if ($femaleIds->has($otherId)) {
                return $otherId;
            }
        }

        return null;
    }
}
