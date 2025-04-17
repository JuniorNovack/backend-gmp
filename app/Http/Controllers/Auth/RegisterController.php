<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Response as Status;
use App\Services\Contracts\IUserService;
use Illuminate\Support\Facades\Response;
use App\Exceptions\NotImplementedException;
use App\Http\Requests\UserRegisteredFormRequest;
use OpenApi\Annotations as OA;


class RegisterController extends Controller
{
    /**
     * @var IUserService
     */
    protected $userService;

    public function __construct(IUserService $userService)
    {
        $this->userService = $userService;

        $this->middleware('permission:create_companies', ['only' => ['createAccount']]);
        $this->middleware('permission:edit_companies', ['only' => ['update']]);
        $this->middleware('permission:delete_companies', ['only' => ['destroy']]);
    }
    /**
     *@OA\Post(
     *     path="/api/v1/user/register",
     *     summary="Créer un compte utilisateur par le manager",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password" ,"ip"},
     *             @OA\Property(property="name", type="string", example="Jean"),
     *             @OA\Property(property="email", type="string", format="email", example="jean@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="12345678"),
     *             @OA\Property(property="ip", type="string", example="192.168.1.1")
     *         )
     *     ),
     * @OA\Response(
     *          response=201,
     *          description="Compte utilisateur créé avec succès",
     *          @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Jean"),
     *                     @OA\Property(property="email", type="string", example="jean@example.com")
     *                 )
     *             )
     *         )
     * ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function createAccount(UserRegisteredFormRequest $request): JsonResponse
    {
        $user = new User([
            'name' => $request->input('first_name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $userResponse = $this->userService->createAccount($user);
        $userResponse->assignRole('manager');

        $userResponse['message'] = Lang::get(
            "User account successfully created"
        );

        return Response::success($userResponse, Status::HTTP_CREATED);
    }


    public function activeCodePassword(Request $request): JsonResponse
    {
        $this->validate($request, ['pin' => 'required|string|min:100000|max:999999']);
        $pin = $request->input('pin');
        $user = $this->userService->getUserFromGuard();

        if ($this->userService->getLastActivationCode($user) !== $pin) {
            return Response::error(Lang::get('Invalid activation code'), Status::HTTP_BAD_REQUEST);
        }

        if (!$user->passwordResets()->delete()) {
            return Response::error('Failed to delete user pins');
        }

        return Response::success($user);
    }


    /**
     * Resent code
     *
     * @throws NotImplementedException
     */
    public function resendActivationCode(): JsonResponse
    {
        $user = $this->userService->getUserFromGuard();

        $data = $this->userService->resentActivationCode($user);

        $data['message'] = Lang::get(
            "Please check your email or phone a new pin has just been sent."
        );
        return Response::success($data);
    }
}
