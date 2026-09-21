<?php

namespace App\Console\Commands;

use App\Album;
use App\Block;
use App\Chat;
use App\Friend;
use App\FriendRequest;
use App\ImageGet;
use App\Like;
use App\Message;
use App\Notification;
use App\Post;
use App\RouletteUser;
use App\User;
use App\User_Pack;
use App\WebAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Per the client's explicit request: a visitor who gets an auto-registered ("fake") account
 * (AutoRegisterController::fakeregister()) but never finishes the "Finish your registration"
 * popup (layouts/layout.blade.php) - i.e. abandons the site mid-way - should have that account
 * deleted rather than left sitting in the database forever.
 *
 * There's no reliable way to detect "the visitor closed the tab" from the server side (the
 * browser's beforeunload/pagehide events aren't guaranteed to fire or complete in time), so
 * this takes the standard alternative: a scheduled sweep (see Kernel::schedule()) that deletes
 * any auto-registered account still carrying its original auto-generated email address (i.e.
 * never completed registration - AutoRegisterController::complete_fake() overwrites it with
 * the visitor's real one) once it's older than the grace period. A visitor who comes back later
 * on the same browser simply gets a fresh account (see the explanation of the remember-me
 * cookie / redirect_autoregister_fake() in helpers.php - nothing re-creates the deleted one).
 */
class DeleteAbandonedAutoRegisteredUsers extends Command
{
    protected $signature = 'users:delete-abandoned-autoregistered {--minutes=30 : How old (in minutes) an unfinished auto-registered account must be before it is deleted} {--dry-run : List the matching accounts without deleting anything}';

    protected $description = 'Deletes auto-registered ("fake") accounts that never completed registration within the grace period';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! $host) {
            $this->error('Could not determine the app host from config(\'app.url\') - aborting.');
            return self::FAILURE;
        }

        // Matches exactly the address AutoRegisterController::fakeregister() generates
        // ($firstname.$lastname.time()."@".$_SERVER['SERVER_NAME']) - still present means the
        // visitor never submitted the "Finish your registration" form, which is the only place
        // that overwrites it with a real address (complete_fake()). role/gender are the same
        // fixed values fakeregister() always sets, kept here as an extra safety net so this
        // can never touch anything but genuinely auto-registered accounts.
        // fakeregister() builds the address from $_SERVER['SERVER_NAME'], which on production is
        // "www.trovamequi.me" (confirmed from live data) while APP_URL's host is the bare domain -
        // matching only '%@<host>' silently skipped every www. one. Both variants are matched.
        $bareHost = preg_replace('/^www\./i', '', $host);

        $users = User::where(function ($query) use ($bareHost) {
                $query->where('email', 'like', '%@' . $bareHost)
                    ->orWhere('email', 'like', '%@www.' . $bareHost);
            })
            ->where('role', 'user')
            ->where('gender', 'male')
            ->where('created_at', '<', now()->subMinutes($minutes))
            ->get(['id', 'email', 'created_at']);

        if ($this->option('dry-run')) {
            foreach ($users as $user) {
                $this->line("{$user->id}\t{$user->email}\t{$user->created_at}");
            }
            $this->info("DRY RUN: {$users->count()} account(s) would be deleted (grace period: {$minutes} minutes). Nothing was deleted.");
            return self::SUCCESS;
        }

        foreach ($users as $user) {
            $this->deleteUserCascade($user->id);
        }

        $message = "Deleted {$users->count()} abandoned auto-registered account(s) (grace period: {$minutes} minutes).";
        $this->info($message);
        if ($users->count() > 0) {
            Log::info('[users:delete-abandoned-autoregistered] ' . $message);
        }

        return self::SUCCESS;
    }

    /**
     * Same cascade WebUsersController::delete() (the admin "delete user" action) already uses -
     * duplicated here rather than shared, since that method is entangled with the admin
     * controller's auth/redirect flow and isn't set up to be called from console context.
     */
    private function deleteUserCascade(int $id): void
    {
        $images = ImageGet::where('user_id', $id)->get();
        foreach ($images as $image) {
            Storage::delete('public/images/' . $image->name);
        }

        Album::where('user_id', $id)->delete();
        Block::where('user', $id)->orWhere('block', $id)->delete();
        Chat::where('from_user', $id)->orWhere('to_user', $id)->delete();
        Friend::where('user', $id)->orWhere('user_friend', $id)->delete();
        FriendRequest::where('user_from', $id)->orWhere('user_to', $id)->delete();
        ImageGet::where('user_id', $id)->delete();
        Like::where('user_id', $id)->delete();
        Message::where('from_user', $id)->orWhere('to_user', $id)->delete();
        Notification::where('user_id', $id)->orWhere('for_user', $id)->delete();
        Post::where('user_id', $id)->delete();
        User_Pack::where('user_id', $id)->delete();
        WebAccount::where('user_id', $id)->delete();
        RouletteUser::where('user_id', $id)->delete();
        User::where('id', $id)->delete();
    }
}
