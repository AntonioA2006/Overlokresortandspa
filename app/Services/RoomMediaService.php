<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RoomMediaService
{
    public function storePublicImage(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

        return 'storage/'.$path;
    }

    public function deletePublicPath(?string $storedPath): void
    {
        if (! filled($storedPath) || ! str_starts_with($storedPath, 'storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($storedPath, strlen('storage/')));
    }
}
