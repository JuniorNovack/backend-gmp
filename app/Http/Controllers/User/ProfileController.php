<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use App\Services\Contracts\IUserService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UpdateUserFromRequest;

class ProfileController extends Controller
{
    protected $userService;

    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }

    public function updateUserProfile($id, UpdateUserFromRequest $request): JsonResponse
    {

        $user = $this->userService->findModelById($id);
        $user->fill($request->toArray());
        $userUpdated = $this->userService->updateUser($user);

        if ($request->has('image')) {
            $userUpdated->addMedia($request->input('image'))->toMediaCollection("user");
        }

        $userUpdated['message'] = 'user updated successfully';

        return Response::success($userUpdated);
    }

    public function deleteProfile($user_id): JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            if ($user->id === $user_id) {
                $this->userService->destroyProfile($user);
            } else {
                $user = $this->userService->findModelById($user_id);
                $this->userService->destroyProfile($user);
            }
        } else {
            $user = $this->userService->findModelById($user_id);
            $this->userService->destroyProfile($user);
        }

        return Response::success(['message' => "User deleted successfuly !"]);
    }

    public function asResetedPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // 'email' => 'required|string|email:users:unique',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors());
        }

        // $email = $request->input('email');

        $userFind = $this->userService->getUserFromGuard();
        $userFind->password = Hash::make($request->input('password')); // Hashage du nouveau mot de passe

        $userResponse = $this->userService->updateUser($userFind);
        return Response::success($userResponse['message'] = Lang::get('Password reseted succefully'));
    }

    public function updateCurrentUserPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors());
        }

        $arrayDatas = [
            'old_password' => $request->input('old_password'),
            'password' => $request->input('password')
        ];

        $userResponse = $this->userService->changeUserPassword($arrayDatas);

        return Response::success($userResponse['message'] = Lang::get('Password reseted succefully'));
    }
}
