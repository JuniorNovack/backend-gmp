<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MediaFile;
use App\Models\MediaFolder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\TemplateStatusEnum;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;

class MediaFileController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = MediaFile::where('owner_id', Auth::id())
                ->with(['folder', 'templates']);

            if ($request->has('folder_id')) {
                $query->where('folder_id', $request->folder_id);
            }

            $response = $query->get();
            return Response::success($response);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }

    public function store(Request $request)
    {
        try {
            $params = $request->all();
            $validator = Validator::make($params, [
                'file' => 'required_without:url|file|mimes:jpg,jpeg,png,gif,mp4,pdf|max:2048',
                'folder_id' => 'required|exists:media_folders,id',
                'duration' => 'nullable|integer',
                'url' => 'required_without:file|url',
            ]);

            if ($validator->fails()) {
                return Response::error($validator->errors()->first());
            }


            $type = null;
            $path = null;
            $fileDetails = [];

            # check whether url is passed or not 
            if (!empty($params['url'])) {
                $type = TemplateStatusEnum::MEDIA_FILE_TYPE_URL;
                $path = $params['url'];
            } else {
                $file = $request->file('file');

                if (!$file || !$file->isValid()) {
                    return Response::error('Invalid file upload');
                }

                $mimeType = $file->getMimeType();
                $extension = strtolower($file->getClientOriginalExtension());
                $type = $this->determineFileType($mimeType, $extension);

                $path = $file->store(
                    'media/' . Auth::id(),
                    'public'
                );

                $fileDetails = [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $mimeType,
                ];
            }

            $response = MediaFile::create([
                'name' => $fileDetails['original_name'] ?? basename($params['url'] ?? ''),
                'path' => $path,
                'folder_id' => $params['folder_id'],
                'owner_id' => Auth::id(),
                'type' => $type,
                'url' => $params['url'] ?? null,
                'duration' => $params['duration'] ?? null,
                'metadata' => $fileDetails,
            ]);

            return Response::success($response);
        } catch (\Exception $e) {
            return Response::error([]);
        }
    }

    public function show($id)
    {
        try {
            $mediaFile = MediaFile::where('owner_id', Auth::id())
                ->with('folder')
                ->findOrFail($id);

            return Response::success([
                'file' => $mediaFile,
                'url' => $mediaFile->type === TemplateStatusEnum::MEDIA_FILE_TYPE_URL
                    ? $mediaFile->path
                    : asset('storage/' . $mediaFile->path)
            ]);
        } catch (\Exception $e) {
            return Response::error('Media file not found', 404);
        }
    }


    public function destroy(MediaFile $file)
    {
        try {
            $this->authorize('delete', $file);

            # Check if file is used in any templates before deletion
            if ($file->templates()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete file - it is used in templates'
                ], 422);
            }

            Storage::disk('public')->delete($file->path);
            $file->delete();

            return response()->json(['message' => 'MediaFile deleted successfully'], 200);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }

    protected function determineFileType(string $mimeType, string $extension): string
    {
        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $videoMimes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
        $documentMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if (Str::startsWith($mimeType, 'image/') || in_array($mimeType, $imageMimes)) {
            return TemplateStatusEnum::MEDIA_FILE_TYPE_IMAGE;
        }

        if (Str::startsWith($mimeType, 'video/') || in_array($mimeType, $videoMimes)) {
            return TemplateStatusEnum::MEDIA_FILE_TYPE_VIDEO;
        }

        if (in_array($mimeType, $documentMimes) || in_array($extension, ['pdf', 'doc', 'docx'])) {
            return TemplateStatusEnum::MEDIA_FILE_TYPE_DOCUMENT;
        }

        return TemplateStatusEnum::MEDIA_FILE_TYPE_DOCUMENT;
    }
}
