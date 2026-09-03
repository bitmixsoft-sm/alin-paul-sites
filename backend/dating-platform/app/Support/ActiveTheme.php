<?php

declare(strict_types=1);

namespace App\Support;

use App\Settings;

/**
 * Resolves the admin-selected site skin (see AdminThemeController / SITE_ACTIVE_THEME in the
 * `settings` table). Blade's `@extends` renders the child view's sections BEFORE the parent
 * layout runs, so a plain `@php $activeTheme = ...` in layouts/layout.blade.php is NOT visible
 * inside a view that `@extends` it (only inside things the layout itself `@include`s, like
 * components/header.blade.php) - this helper is called independently wherever the active
 * theme is needed, with a per-request static cache so it's still just one query.
 */
final class ActiveTheme
{
    private const VALID = ['classic', 'aurora', 'nordic', 'volt', 'velvet', 'bloom', 'binder', 'rosewood'];

    private static ?string $cached = null;

    public static function current(): string
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $value = Settings::where('name', 'SITE_ACTIVE_THEME')->value('value') ?: 'classic';

        return self::$cached = in_array($value, self::VALID, true) ? $value : 'classic';
    }

    /**
     * @return list<string> all valid theme slugs, in the order they should be offered in a
     *     picker - used by the frontend quick-switch widget (layouts/layout.blade.php) so it
     *     doesn't need to duplicate this list.
     */
    public static function available(): array
    {
        return self::VALID;
    }
}
