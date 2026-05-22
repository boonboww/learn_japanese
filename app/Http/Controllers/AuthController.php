<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use OpenApi\Attributes as OA;


class AuthController extends Controller implements HasMiddleware
{
    /**
     * Define the middleware that should be applied to the controller.
     */
    public static function middleware(): array
    {
        return [
            // Require auth for all methods except login and register
            new Middleware('auth:api', except: ['login', 'register']),
        ];
    }

    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */


    #[OA\Post(
        path: "/auth/register",
        summary: "Register a new user",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "username", type: "string", example: "Dong"),
                    new OA\Property(property: "email", type: "string", example: "[EMAIL_ADDRESS]"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "12345678"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "12345678"),
                    new OA\Property(property: "avatar", type: "string", nullable: true, example: "https://example.com/avatar.png")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Registered successfully"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'avatar' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => $request->avatar,
        ]);

        // Generate token for the newly registered user
        $auth = auth('api');
        assert($auth instanceof \PHPOpenSourceSaver\JWTAuth\JWTGuard);
        $token = $auth->login($user);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký tài khoản thành công.',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => $auth->factory()->getTTL() * 60
            ]
        ], 201);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Post(path: "/auth/login", summary: "Login", tags: ["Auth"],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: "email", type: "string", example: "[EMAIL_ADDRESS]"),
            new OA\Property(property: "password", type: "string", example: "12345678")
        ])),
        responses: [
            new OA\Response(response: 200, description: "Success", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "user", ref: "#/components/schemas/User"),
                new OA\Property(property: "authorisation", type: "object")
            ])),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        $auth = auth();
        assert($auth instanceof \PHPOpenSourceSaver\JWTAuth\JWTGuard);
        if (!$token = $auth->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin đăng nhập không chính xác.'
            ], 401);
        }

        $user = $auth->user();

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => $auth->factory()->getTTL() * 60
            ]
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     * 
     * @return \Illuminate\Http\JsonResponse
     */

    #[OA\Post(path: "/auth/logout", summary: "Logout", security: [["bearerAuth" => []]], tags: ["Auth"],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function logout()
    {
        $auth = auth();
        assert($auth instanceof \PHPOpenSourceSaver\JWTAuth\JWTGuard);
        $auth->logout();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công.'
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    #[OA\Post(path: "/auth/refresh", summary: "Refresh Token", security: [["bearerAuth" => []]], tags: ["Auth"],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]

    public function refresh()
    {
        $auth = auth();
        assert($auth instanceof \PHPOpenSourceSaver\JWTAuth\JWTGuard);
        return response()->json([
            'success' => true,
            'user' => $auth->user(),
            'authorisation' => [
                'token' => $auth->refresh(),
                'type' => 'bearer',
                'expires_in' => $auth->factory()->getTTL() * 60
            ]
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Get(path: "/auth/me", summary: "Get Profile", security: [["bearerAuth" => []]], tags: ["Auth"],
        responses: [
            new OA\Response(response: 200, description: "Success", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "user", ref: "#/components/schemas/User")
            ]))
        ]
    )]

    public function me()
    {
        $auth = auth();
        assert($auth instanceof \PHPOpenSourceSaver\JWTAuth\JWTGuard);
        return response()->json([
            'success' => true,
            'user' => $auth->user()
        ]);
    }
}
