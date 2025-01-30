<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Endpoints de autenticação"
 * )
 */
class AuthController extends Controller
{

    /**
     * Registrar um novo usuário.
     *
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Cria um novo usuário",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "cpf", "email", "password"},
     *             @OA\Property(property="name", type="string", example="Usuário Teste"),
     *             @OA\Property(property="cpf", type="string", example="12345678901"),
     *             @OA\Property(property="email", type="string", format="email", example="usuario@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="senhaSegura")
     *         ),
     *     ),
     *     @OA\Response(response=201, description="Usuário registrado com sucesso"),
     *     @OA\Response(response=422, description="Erro de validação"),
     * )
     */

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|max:11|unique:users,cpf',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);
    
        $user = User::create([
            'name' => $validated['name'],
            'cpf' => $validated['cpf'], 
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'status' => true,
            'message' => 'Usuário registrado com sucesso!',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Fazer login na API.
     *
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Realiza login na API",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="senhaSegura")
     *         ),
     *     ),
     *     @OA\Response(response=200, description="Login bem-sucedido"),
     *     @OA\Response(response=401, description="Credenciais inválidas"),
     * )
     */

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login bem-sucedido!',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Fazer logout da API.
     *
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Desloga o usuário",
     *     security={{ "sanctum": {} }},
     *     @OA\Response(response=200, description="Logout realizado com sucesso"),
     *     @OA\Response(response=401, description="Token inválido"),
     * )
     */

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout realizado com sucesso!',
        ]);
    }
}
