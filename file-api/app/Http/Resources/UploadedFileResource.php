<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UploadedFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'type' => $this->type,
            'uploaded_at' => $this->created_at->toDateTimeString(),
            'preview_url' => route('files.preview', ['id' => $this->id]),
        ];
    }
}
