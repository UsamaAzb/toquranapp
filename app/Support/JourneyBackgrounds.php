<?php

namespace App\Support;

/**
 * Selects and persists a journey background image path for the current session.
 *
 * The stored path keeps the background stable across page reloads during the
 * same browser session. A random replacement is selected when the stored path
 * no longer matches an available background asset.
 */
class JourneyBackgrounds
{
    private const SESSION_KEY = 'w14_journey_background_path';

    private const FALLBACK_PATH = 'images/journey/background34.webp';

    /**
     * Return the absolute URL for the current session's journey background image.
     */
    public static function currentUrl(): string
    {
        return asset(self::currentPath());
    }

    /**
     * Return the relative asset path for the current session's journey background.
     */
    private static function currentPath(): string
    {
        $storedPath = (string) session(self::SESSION_KEY, '');

        if (self::isAvailableBackgroundPath($storedPath)) {
            return $storedPath;
        }

        $path = self::randomPath();
        session([self::SESSION_KEY => $path]);

        return $path;
    }

    /**
     * Select a random available background path, falling back to the default.
     */
    private static function randomPath(): string
    {
        $files = glob(public_path('images/journey/background*.webp')) ?: [];

        $paths = array_values(array_map(
            fn (string $file): string => 'images/journey/'.basename($file),
            $files
        ));

        $paths = array_values(array_filter(
            $paths,
            fn (string $path): bool => self::isBackgroundPath($path)
        ));

        if ($paths === []) {
            return self::FALLBACK_PATH;
        }

        return $paths[array_rand($paths)];
    }

    /**
     * Return true when the path matches the expected background filename pattern.
     */
    private static function isBackgroundPath(string $path): bool
    {
        return preg_match('/^images\/journey\/background[0-9]+\.webp$/', $path) === 1;
    }

    /**
     * Return true when the path is a valid background path and exists on disk.
     */
    private static function isAvailableBackgroundPath(string $path): bool
    {
        return self::isBackgroundPath($path)
            && is_file(public_path($path));
    }
}
