<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SupabaseStorageService
{
    private string $publicDisk = 'supabase_public';
    private string $privateDisk = 'supabase_private';

    public function uploadPublic(UploadedFile $file,string $directory): string {
        return $this->upload(
            $file,
            $directory,
            $this->publicDisk
        );
    }

    public function uploadPrivate(UploadedFile $file,string $directory): string {
        return $this->upload(
            $file,
            $directory,
            $this->privateDisk
        );
    }

    private function upload(UploadedFile $file,string $directory,string $disk): string {
        $fileName = Str::uuid()
            . '.'
            . $file->getClientOriginalExtension();

        $path = trim($directory, '/')
            . '/'
            . $fileName;

        Storage::disk($disk)->put(
            $path,
            file_get_contents($file->getRealPath())
        );

        return $path;
    }

    public function deletePublic(?string $path): void
    {
        $this->delete($path, $this->publicDisk);
    }

    public function deletePrivate(?string $path): void
    {
        $this->delete($path, $this->privateDisk);
    }

    private function delete(?string $path,string $disk): void {
        if (!$path) {
            return;
        }

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    public function publicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk($this->publicDisk)
            ->url($path);
    }

    public function temporaryPrivateUrl(?string $path,int $minutes = 5): ?string {
        if (!$path) {
            return null;
        }

        return Storage::disk($this->privateDisk)
            ->temporaryUrl(
                $path,
                now()->addMinutes($minutes)
            );
    }
}