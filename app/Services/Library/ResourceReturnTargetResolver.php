<?php

namespace App\Services\Library;

use Illuminate\Http\Request;

class ResourceReturnTargetResolver
{
    public function resolveFromRequest(Request $request, ?string $fallback = null, string $lastResort = '/'): string
    {
        return $this->safeSameOriginUrl($request->query('return_to'))
            ?? $this->safeSameOriginUrl($fallback)
            ?? $this->safeSameOriginUrl($lastResort)
            ?? url('/');
    }

    public function safeSameOriginUrl(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $candidate = parse_url($value);

        if ($candidate === false) {
            return null;
        }

        $app = parse_url(url('/'));

        if (isset($candidate['scheme']) || isset($candidate['host'])) {
            if (($candidate['scheme'] ?? null) !== ($app['scheme'] ?? null)) {
                return null;
            }

            if (($candidate['host'] ?? null) !== ($app['host'] ?? null)) {
                return null;
            }

            $candidatePort = $candidate['port'] ?? $this->defaultPort($candidate['scheme'] ?? null);
            $appPort = $app['port'] ?? $this->defaultPort($app['scheme'] ?? null);

            if ($candidatePort !== $appPort) {
                return null;
            }

            return $value;
        }

        if (! str_starts_with($value, '/')) {
            return null;
        }

        if (str_starts_with($value, '//')) {
            return null;
        }

        return url($value);
    }

    private function defaultPort(?string $scheme): ?int
    {
        return match ($scheme) {
            'http' => 80,
            'https' => 443,
            default => null,
        };
    }
}
