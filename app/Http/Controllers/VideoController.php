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
}
