<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class Controller
{
    protected function trustedExternalAttachmentUrl(?string $url): ?string
    {
        return Helpers::trustedExternalAttachmentUrl($url);
    }

    protected function safeDownloadFilename(string $path, string $fallback = 'attachment'): string
    {
        $filename = basename(str_replace('\\', '/', $path));
        $filename = preg_replace('/[\x00-\x1F\x7F\/\\\\]+/u', '_', $filename) ?: '';
        $filename = trim($filename);

        return $filename !== '' ? $filename : $fallback;
    }

    protected function safeAsciiFilenameFallback(string $filename, string $fallback = 'attachment'): string
    {
        $asciiFilename = preg_replace('/[^A-Za-z0-9._-]+/', '_', $filename) ?: '';
        $asciiFilename = trim($asciiFilename, '.');

        return $asciiFilename !== '' ? $asciiFilename : $fallback;
    }

    protected function attachmentDownloadResponse(string $absolutePath, string $downloadName, array $headers = []): BinaryFileResponse
    {
        return response()
            ->download($absolutePath, $downloadName, $headers)
            ->setContentDisposition(
                'attachment',
                $downloadName,
                $this->safeAsciiFilenameFallback($downloadName)
            );
    }

    protected function inlineAttachmentResponse(string $absolutePath, string $downloadName, array $headers = []): BinaryFileResponse
    {
        return response()
            ->file($absolutePath, $headers)
            ->setContentDisposition(
                'inline',
                $downloadName,
                $this->safeAsciiFilenameFallback($downloadName)
            );
    }

    protected function storageAttachmentDownloadResponse(
        string $disk,
        string $path,
        string $downloadName,
        array $headers = []
    ): StreamedResponse {
        return $this->storageAttachmentResponse($disk, $path, 'attachment', $downloadName, $headers);
    }

    protected function storageInlineAttachmentResponse(
        string $disk,
        string $path,
        string $downloadName,
        array $headers = []
    ): StreamedResponse {
        return $this->storageAttachmentResponse($disk, $path, 'inline', $downloadName, $headers);
    }

    private function storageAttachmentResponse(
        string $disk,
        string $path,
        string $disposition,
        string $downloadName,
        array $headers = []
    ): StreamedResponse {
        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            abort(404, 'File not found.');
        }

        $mimeType = $storage->mimeType($path);
        $size = $storage->size($path);

        if ($mimeType !== false && ! isset($headers['Content-Type'])) {
            $headers['Content-Type'] = $mimeType;
        }

        if ($size !== false && ! isset($headers['Content-Length'])) {
            $headers['Content-Length'] = (string) $size;
        }

        $response = response()->stream(function () use ($disk, $storage, $path): void {
            $stream = $storage->readStream($path);

            if ($stream === false) {
                report(new RuntimeException("Unable to open attachment stream [{$disk}:{$path}]."));

                return;
            }

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $headers);

        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                $disposition,
                $downloadName,
                $this->safeAsciiFilenameFallback($downloadName)
            )
        );

        return $response;
    }
}
