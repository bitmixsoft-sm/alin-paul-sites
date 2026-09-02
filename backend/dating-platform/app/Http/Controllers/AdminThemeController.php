<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Settings;
use App\Support\ActiveTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminThemeController extends Controller
{
    /**
     * @var array<string, array{label: string, description: string}>
     */
    private const THEMES = [
        'classic' => [
            'label' => 'Classic',
            'description' => 'The current, original design - unchanged.',
        ],
        'aurora' => [
            'label' => 'Aurora',
            'description' => 'Dark theme with pink/amber/violet accents and a pulsing online indicator.',
        ],
        'nordic' => [
            'label' => 'Nordic',
            'description' => 'Dark theme with pink/teal/gold accents, bold display type.',
        ],
        'volt' => [
            'label' => 'Volt',
            'description' => 'Bold neo-brutalist look - thick borders, hard offset shadows, neon lime/pink on near-black.',
        ],
        'velvet' => [
            'label' => 'Velvet',
            'description' => 'Luxury theme with serif display type and a burgundy/near-black/gold palette.',
        ],
        'bloom' => [
            'label' => 'Bloom',
            'description' => 'Bright warm theme with a full-width masonry profile grid and a cream/coral/teal palette.',
        ],
        'binder' => [
            'label' => 'Binder',
            'description' => 'Collectible trading-card look - holographic rotating-border cards that reveal actions on hover, colorful file-tab navigation.',
        ],
    ];

    public function index(): View
    {
        $on_page = 'Aspect';
        $activeTheme = ActiveTheme::current();
        $themes = self::THEMES;

        return view('admin.themes', compact('on_page', 'activeTheme', 'themes'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:' . implode(',', array_keys(self::THEMES))],
        ]);

        Settings::updateOrInsert(
            ['name' => 'SITE_ACTIVE_THEME'],
            ['value' => $validated['theme'], 'type' => 'select|' . implode('~', array_keys(self::THEMES))]
        );

        // back() (not a hardcoded /admin/themes redirect) so this same endpoint also works for
        // the frontend quick-switch widget (layouts/layout.blade.php, admin/editor only) -
        // submitted from whatever page the admin was actually looking at, it should return
        // them there instead of bouncing them to the admin panel.
        return back()->with('status', 'Theme updated successfully.');
    }
}
