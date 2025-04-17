<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MediaFolder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;



class MediaFolderController extends Controller
{
    public function index()
    {
        try {
            $response =  MediaFolder::where('owner_id', Auth::id())
                ->with(['children', 'files'])
                ->whereNull('parent_id')
                ->get();

            return Response::success($response);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }

    public function store(Request $request): JsonResponse
    {

        try {
            $params = $request->all();
            $validator = Validator::make($params, [
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:media_folders,id',
            ]);

            if ($validator->fails()) {
                return  Response::error($validator->getMessageBag());
            }

            $response = MediaFolder::create([
                'name' => $params['name'],
                'parent_id' => $params['parent_id'],
                'owner_id' => Auth::id(),
            ]);
            return Response::success($response);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }

    public function show(MediaFolder $folder)
    {
        try {
            $this->authorize('view', $folder);

            $response = $folder->load(['children', 'files']);
            return Response::success($response);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }

    public function update(Request $request, MediaFolder $folder)
    {
        try {
            $this->authorize('update', $folder);

            $params = $request->all();
            $validator = Validator::make($params, [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return  Response::error($validator->getMessageBag());
            }

            $response = $folder->update($params);
            return Response::success($response);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }

    public function destroy(MediaFolder $folder)
    {
        try {
            $this->authorize('delete', $folder);

            if ($folder->files()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete folder - it contains files'
                ], 422);
            }

            $folder->delete();
            return response()->json(['message' => 'MediaFolder deleted successfully'], 200);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }
}
