<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exceptions\ModelException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use App\Http\Requests\LoginFormRequest;
use App\Services\Contracts\IUserService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\ModelNotFoundException;
use App\Exceptions\NotImplementedException;
use Illuminate\Http\Response as HTTPResponse;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    protected IUserService $userService;
    /**
     * AuthController constructor.
     * @param IUserService $userService
     */
    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/login",
     *     summary="Créer un compte utilisateur",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "email", "password", "account_type", "ip"},
     *             @OA\Property(property="first_name", type="string", example="Jean"),
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678"),
     *             @OA\Property(property="account_type", type="string", example="manager"),
     *             @OA\Property(property="ip", type="string", example="192.168.1.1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User account successfully created"),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJK..."),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Jean"),
     *                 @OA\Property(property="email", type="string", example="jean@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function openSession(LoginFormRequest $request): JsonResponse
    {
        
        $email = $request->get('email');

        if (!$this->userService->hasValidCredentials($email, $request->get('password'))) {
            $reason = Lang::get("'email' & 'password' do not match with our records");
            return Response::error($reason, HTTPResponse::HTTP_FORBIDDEN);
        }

        $user = $this->userService->findModelBy('email', $email);

        $notify = false;
        
        $response = $this->userService->generateUserTokenFromIp($user, $request->get('ip'), $notify);

        return Response::success($response);
    }

    /**
     * usernvaluserdates user session
     *
     * @return JsonResponse
     * @throws ModelNotFoundException|ModelException
     */
    public function closeSession(): JsonResponse
    {
        $user = $this->userService->getUserFromGuard();
        $this->userService->destroySession($user);

        return Response::success(Lang::get('Session successfully closed.'));
    }

    /**
     * @throws NotImplementedException
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'ip' => 'required|ip'
        ]);

        if ($validator->fails()) {
            return Response::error($validator->errors());
        }

        $user = $this->userService->getUserByEmail($request->input('email'));
        $notify = true;

        $response = $this->userService->generateUserTokenFromIp($user, $request->input('ip'), $notify);

        return Response::success($response);
    }

    public function showAuthUSer()
    {
        return Response::success($this->userService->getUserFromGuard());
    }

    public function getUser($id)
    {
        return Response::success($this->userService->findModelById($id));
    }
}
