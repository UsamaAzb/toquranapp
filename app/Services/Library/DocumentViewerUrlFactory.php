<?php

namespace App\Services\Library;

use Illuminate\Support\Facades\Config;

class DocumentViewerUrlFactory
{
    public const PROVIDER_GOOGLE = 'google';

    public const PROVIDER_MICROSOFT = 'microsoft';

    public const PROVIDER_NATIVE = 'native';

    public const PROVIDER_DOWNLOAD = 'download';

    /** @return array{provider:string, viewer_url:string|null, public_url:string|null} */
    public function forPublicStorageFile(string $path, string $extension, ?string $nativeUrl = null): array
    {
        $extension = strtolower($extension);
        $publicUrl = $this->publicStorageUrl($path);

        if ($extension === 'pdf') {
            return $this->forPdf($publicUrl, $nativeUrl);
        }

        if ($this->isOfficeExtension($extension)) {
            return $this->forOffice($publicUrl);
        }

        return [
            'provider' => self::PROVIDER_NATIVE,
            'viewer_url' => $nativeUrl,
            'public_url' => $publicUrl,
        ];
    }

    public function publicStorageUrl(string $path): ?string
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        if ($path === '') {
            return null;
        }

        $baseUrl = (string) Config::get('filesystems.disks.public.url', '');
        $baseUrl = $baseUrl !== '' ? $baseUrl : url('/storage');

        if (str_starts_with($baseUrl, '/')) {
            $baseUrl = url($baseUrl);
        }

        $encodedPath = collect(explode('/', $path))
            ->map(fn (string $segment): string => rawurlencode($segment))
            ->implode('/');

        return rtrim($baseUrl, '/').'/'.$encodedPath;
    }

    /** @return array{provider:string, viewer_url:string|null, public_url:string|null} */
    private function forPdf(?string $publicUrl, ?string $nativeUrl): array
    {
        $provider = strtolower((string) Config::get('document-viewer.pdf_provider', self::PROVIDER_GOOGLE));

        if ($provider === self::PROVIDER_NATIVE) {
            return [
                'provider' => self::PROVIDER_NATIVE,
                'viewer_url' => $nativeUrl,
                'public_url' => $publicUrl,
            ];
        }

        if ($this->isLoopbackUrl($publicUrl)) {
            return [
                'provider' => self::PROVIDER_NATIVE,
                'viewer_url' => $nativeUrl,
                'public_url' => $publicUrl,
            ];
        }

        if ($publicUrl === null) {
            return [
                'provider' => self::PROVIDER_DOWNLOAD,
                'viewer_url' => null,
                'public_url' => null,
            ];
        }

        return [
            'provider' => self::PROVIDER_GOOGLE,
            'viewer_url' => 'https://docs.google.com/gview?embedded=true&url='.rawurlencode($publicUrl),
            'public_url' => $publicUrl,
        ];
    }

    /** @return array{provider:string, viewer_url:string|null, public_url:string|null} */
    private function forOffice(?string $publicUrl): array
    {
        $provider = strtolower((string) Config::get('document-viewer.office_provider', self::PROVIDER_MICROSOFT));

        if ($provider !== self::PROVIDER_MICROSOFT || $publicUrl === null || $this->isLoopbackUrl($publicUrl)) {
            return [
                'provider' => self::PROVIDER_DOWNLOAD,
                'viewer_url' => null,
                'public_url' => $publicUrl,
            ];
        }

        return [
            'provider' => self::PROVIDER_MICROSOFT,
            'viewer_url' => 'https://view.officeapps.live.com/op/embed.aspx?src='.rawurlencode($publicUrl),
            'public_url' => $publicUrl,
        ];
    }

    private function isOfficeExtension(string $extension): bool
    {
        return in_array($extension, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'], true);
    }

    private function isLoopbackUrl(?string $url): bool
    {
        if ($url === null) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
    }
}
