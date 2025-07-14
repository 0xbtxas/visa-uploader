<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadFileRequest;
use App\Http\Resources\UploadedFileResource;
use App\Models\UploadedFile;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Handles file upload, retrieval, deletion, and preview operations.
 */
class FileController extends Controller
{
    /**
     * Injects the FileService to handle file-related operations.
     *
     * @param FileService $fileService
     */
    public function __construct(private FileService $fileService) {}

    /**
     * Handles uploading a file and stores its metadata.
     *
     * @param UploadFileRequest $request Validated upload request containing file and type.
     * @return JsonResponse JSON response containing the uploaded file metadata.
     */
    public function upload(UploadFileRequest $request): JsonResponse
    {
        try {
            $file = $this->fileService->store($request->file('file'), $request->input('type'));

            return response()->json([
                'message' => 'File uploaded successfully.',
                'data' => new UploadedFileResource($file),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'File upload failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Returns a JSON response containing all uploaded files grouped by type.
     *
     * @return JsonResponse Grouped list of uploaded files by type.
     */
    public function index(): JsonResponse
    {
        $types = UploadedFile::TYPES;
        $files = UploadedFile::all()->groupBy('type');

        $grouped = collect($types)->mapWithKeys(function ($type) use ($files) {
            return [
                $type => UploadedFileResource::collection($files->get($type, collect())),
            ];
        });

        return response()->json($grouped);
    }

    /**
     * Deletes a file both from storage and the database by its ID.
     *
     * @param int $id ID of the file to delete.
     * @return JsonResponse Success or error message.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $file = UploadedFile::findOrFail($id);
            $this->fileService->delete($file);

            return response()->json(['message' => 'File deleted successfully.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['message' => 'File not found.'], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'File deletion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Returns a thumbnail preview of a file (image or fallback) by its ID.
     *
     * @param int $id ID of the file to preview.
     * @return StreamedResponse|JsonResponse Thumbnail image stream or error JSON response.
     */
    public function preview(int $id): StreamedResponse|JsonResponse
    {
        $file = UploadedFile::findOrFail($id);
        $thumbnailPath = $this->fileService->getThumbnailPath($file);

        if (!Storage::disk('local')->exists($thumbnailPath)) {
            $generated = $this->fileService->generateThumbnail($file);
            if (!$generated) {
                return response()->json(['error' => 'Thumbnail generation failed or original missing'], 500);
            }
        }

        return Storage::disk('local')->response($thumbnailPath, $file->filename, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'max-age=31536000, public',
        ]);
    }
}
