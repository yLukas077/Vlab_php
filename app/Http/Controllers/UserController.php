<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Gerenciamento de usuários"
 * )
 */
class UserController extends Controller
{
    /**
     * Lista os usuários paginados.
     *
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Lista usuários cadastrados",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página para paginação",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuários retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="users", type="array", @OA\Items(ref="#/components/schemas/User"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $users = User::orderBy('id', 'DESC')->paginate(10);

        return response()->json([
            'status' => true,
            'users' => $users,
        ], 200);
    }

    /**
     * Exibe os detalhes de um usuário específico.
     *
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Obtém informações de um usuário específico",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do usuário",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do usuário retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'status' => true,
            'user' => $user,
        ]);
    }

    /**
     * Atualiza um usuário existente.
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Atualiza informações de um usuário",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do usuário a ser atualizado",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Novo Nome"),
     *             @OA\Property(property="email", type="string", format="email", example="novoemail@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="novaSenhaSegura")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuário atualizado com sucesso!"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Usuário atualizado com sucesso!',
            'data' => $user,
        ], 200);
    }

    /**
     * Deleta um usuário.
     *
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Remove um usuário",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do usuário a ser removido",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário deletado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuário deletado com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Usuário não pode ser deletado devido a transações associadas",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="integer", example=403),
     *                 @OA\Property(property="message", type="string", example="Usuário não pode ser deletado, pois possui transações associadas.")
     *             )
     *         )
     *     )
     * )
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->transactions()->exists()) {
            return response()->json([
                'status' => false,
                'error' => [
                    'code' => 403,
                    'message' => 'Usuário não pode ser deletado, pois possui transações associadas.',
                ],
            ], 403);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Usuário deletado com sucesso!',
        ], 200);
    }
}
