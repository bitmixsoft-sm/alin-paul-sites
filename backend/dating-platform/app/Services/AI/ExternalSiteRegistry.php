<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Lists the external sites configured in config/database.php's externalSiteConnections()
 * (EXTERNAL_SITE_1_*, EXTERNAL_SITE_2_*, ... in .env) - each one a separate deployment of this
 * same codebase (same users/messages schema) on a different domain, e.g. wizoox.com, that a
 * trovamequi.me profile's AI Style Learning can pull a transcript from (client's request,
 * 2026-09-18: "invete de la un profil de pe alt site").
 *
 * Reads the connection array back out of config() rather than re-parsing env() directly, so this
 * stays a single source of truth with config/database.php - and works the same whether or not
 * production ever runs `config:cache`.
 */
final class ExternalSiteRegistry
{
    /**
     * @return array<string, string> connection name => display label, e.g. ['external_site_1' => 'Wizoox']
     */
    public static function list(): array
    {
        $sites = [];

        foreach (config('database.connections', []) as $name => $config) {
            if (str_starts_with($name, 'external_site_')) {
                $sites[$name] = $config['label'] ?? $name;
            }
        }

        return $sites;
    }

    public static function exists(string $connection): bool
    {
        return array_key_exists($connection, self::list());
    }
}
