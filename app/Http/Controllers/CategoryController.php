<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="Gerenciamento de categorias financeiras"
 * )
 */
class CategoryController extends Controller
{
    /**
     * Lista todas as categorias.
     *
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Lista todas as categorias registradas",
     *     security={{ "sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de categorias retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Listar categorias"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Category"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Listar categorias',
            'data' => $categories,
        ]);
    }

    /**
     * Exibe os detalhes de uma categoria específica.
     *
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Obtém detalhes de uma categoria",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da categoria",
     *         required=true,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da categoria retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     )
     * )
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $category,
        ]);
    }

    /**
     * Cria uma nova categoria.
     *
     * @OA\Post(
     *     path="/api/categories",
     *     tags={"Categories"},
     *     summary="Registra uma nova categoria",
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Alimentação")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categoria criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Categoria criada com sucesso!"),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:categories,name',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Categoria criada com sucesso!',
            'data' => $category,
        ], 201);
    }

    /**
     * Atualiza uma categoria existente.
     *
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Atualiza os detalhes de uma categoria",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da categoria a ser atualizada",
     *         required=true,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Transporte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Categoria atualizada com sucesso!"),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Categoria atualizada com sucesso!',
            'data' => $category,
        ], 200);
    }

    /**
     * Remove uma categoria existente.
     *
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Deleta uma categoria",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da categoria a ser deletada",
     *         required=true,
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Categoria deletada com sucesso"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Não é possível deletar uma categoria com transações associadas",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="integer", example=400),
     *                 @OA\Property(property="message", type="string", example="Não é possível deletar uma categoria com transações associadas.")
     *             )
     *         )
     *     )
     * )
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->transactions()->exists()) {
            return response()->json([
                'status' => false,
                'error' => [
                    'code' => 400,
                    'message' => 'Não é possível deletar uma categoria com transações associadas.',
                ],
            ], 400);
        }

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Categoria deletada com sucesso!',
        ], 204);
    }
}
