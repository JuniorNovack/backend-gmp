<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MediaFile;
use App\Models\SharePermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class ShareController extends Controller
{
    public function shareMedia(Request $request, MediaFile $file)
    {
        try {
            $this->authorize('share', $file);

            $params = $request->all();
            $validator = Validator::make($params, [
                'shared_with_id' => 'required|exists:users,id',
                'permission_type' => 'required|in:view,edit',
            ]);

            if ($validator->fails()) {
                return  Response::error($validator->getMessageBag());
            }

            $response = SharePermission::create([
                'media_file_id' => $file->id,
                'shared_with_id' => $params['shared_with_id'],
                'permission_type' => $params['permission_type'],
                'shared_by_id' => Auth::id(),
                'expires_at' => Carbon::now(),
            ]);

            return Response::success($response);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }
    public function listAllShares()
    {
        try {
            # Get files shared by me
            $sharedByMe = SharePermission::where('shared_by_id', Auth::id())
                ->with(['mediaFile', 'sharedWith'])
                ->get();

            # Get files shared with me
            $sharedWithMe = SharePermission::where('shared_with_id', Auth::id())
                ->with(['mediaFile', 'sharedBy'])
                ->get();

            return Response::success([
                'shared_by_me' => $sharedByMe,
                'shared_with_me' => $sharedWithMe
            ]);
        } catch (\Exception $e) {
            return  Response::error([]);
        }
    }
}
