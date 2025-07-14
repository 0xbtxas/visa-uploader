<?php

namespace App\Services;

use App\Models\UploadedFile;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Log;

/**
 * Service responsible for handling file upload, storage, deletion, and thumbnail generation.
 */
class FileService
{
    /**
     * Stores an uploaded file on the local disk and records its metadata in the database.
     *
     * @param HttpUploadedFile $file The uploaded file instance.
     * @param string $type A string indicating the category or context of the file (e.g., "avatar", "document").
     * @return UploadedFile The persisted file record.
     */
    public function store(HttpUploadedFile $file, string $type): UploadedFile
    {
        $path = $file->store("uploads/{$type}", 'local');

        return UploadedFile::create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'type' => $type,
        ]);
    }

    /**
     * Deletes the file from storage and removes its database record.
     *
     * @param UploadedFile $file The file record to delete.
     * @return void
     */
    public function delete(UploadedFile $file): void
    {
        Storage::disk('local')->delete($file->path);
        $file->delete();
    }

    /**
     * Generates a thumbnail image for the given file.
     * - For image files: creates a resized thumbnail.
     * - For PDFs: copies a fallback thumbnail image.
     *
     * @param UploadedFile $file The uploaded file to generate a thumbnail for.
     * @return string|null The thumbnail path if generated successfully, or null on failure.
     */
    public function generateThumbnail(UploadedFile $file): ?string
    {
        $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
        $originalPath = $file->path;
        $thumbnailPath = "thumbnails/{$file->id}.jpg";

        if (!Storage::disk('local')->exists($originalPath)) {
            return null;
        }

        // Ensure the thumbnail directory exists
        $thumbnailDir = storage_path('app/private/thumbnails');
        if (!file_exists($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        try {
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                // Generate thumbnail from image
                $content = Storage::disk('local')->get($originalPath);
                $image = Image::read($content)
                    ->resize(100, 100, fn($constraint) => $constraint->aspectRatio()->upsize());
                $image->save("{$thumbnailDir}/{$file->id}.jpg");
            } elseif ($ext === 'pdf') {
                // Use fallback image for PDFs
                Storage::disk('local')->copy('fallbacks/pdf.png', $thumbnailPath);
            }
        } catch (\Exception $e) {
            Log::error("Thumbnail generation error: {$e->getMessage()}");
            return null;
        }

        return $thumbnailPath;
    }

    /**
     * Gets the expected thumbnail path for a given uploaded file.
     *
     * @param UploadedFile $file The uploaded file.
     * @return string The thumbnail relative path.
     */
    public function getThumbnailPath(UploadedFile $file): string
    {
        return "thumbnails/{$file->id}.jpg";
    }
}
