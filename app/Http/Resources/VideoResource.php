<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'file_path' => $this->file_path,
            'file_url' => $this->file_path ? Storage::url($this->file_path) : null,
            'file_size' => $this->file_size,
            'file_size_human' => $this->file_size ? $this->formatBytes($this->file_size) : null,
            'duration' => $this->duration,
            'mime_type' => $this->mime_type,
            'thumbnail_path' => $this->thumbnail_path,
            'thumbnail_url' => $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
