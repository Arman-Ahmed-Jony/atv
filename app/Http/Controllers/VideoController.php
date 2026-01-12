<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoUploadRequest;
use App\Http\Resources\VideoResource;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VideoController extends Controller
{
    /**
     * Upload a new video.
     */
    public function upload(VideoUploadRequest $request): JsonResponse
    {
        $file = $request->file('video');
        $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $filePath = $file->storeAs('videos', $fileName, 'public');

        $video = Video::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json([
            'message' => 'Video uploaded successfully',
            'video' => new VideoResource($video),
        ], 201);
    }

    /**
     * List all videos.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $videos = Video::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return VideoResource::collection($videos);
    }

    /**
     * Get a single video.
     */
    public function show(Request $request, Video $video): JsonResponse
    {
        // Ensure user owns the video
        if ($video->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(new VideoResource($video));
    }

    /**
     * Stream video with range request support.
     */
    public function stream(Request $request, Video $video)
    {
        // Ensure user owns the video
        if ($video->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filePath = storage_path('app/public/'.$video->file_path);

        if (! file_exists($filePath)) {
            return response()->json(['message' => 'Video file not found'], 404);
        }

        $fileSize = filesize($filePath);
        $start = 0;
        $end = $fileSize - 1;

        // Handle range requests for streaming
        if ($request->hasHeader('Range')) {
            $range = $request->header('Range');
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = (int) $matches[1];
                $end = $matches[2] ? (int) $matches[2] : $fileSize - 1;
            }
        }

        $length = $end - $start + 1;
        $file = fopen($filePath, 'rb');
        fseek($file, $start);

        return response()->stream(function () use ($file, $length) {
            echo fread($file, $length);
            fclose($file);
        }, 206, [
            'Content-Type' => $video->mime_type,
            'Content-Length' => $length,
            'Content-Range' => "bytes {$start}-{$end}/{$fileSize}",
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Download video for offline storage.
     */
    public function download(Request $request, Video $video)
    {
        // Ensure user owns the video
        if ($video->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $filePath = storage_path('app/public/'.$video->file_path);

        if (! file_exists($filePath)) {
            return response()->json(['message' => 'Video file not found'], 404);
        }

        return response()->download($filePath, basename($video->file_path), [
            'Content-Type' => $video->mime_type,
        ]);
    }
}
