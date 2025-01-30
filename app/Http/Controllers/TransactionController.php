<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Transactions",
 *     description="Gerenciamento de transações financeiras"
 * )
 */
class TransactionController extends Controller
{
    /**
     * Lista todas as transações, com suporte a filtros opcionais.
     *
     * @OA\Get(
     *     path="/api/transactions",
     *     tags={"Transactions"},
     *     summary="Lista transações registradas",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filtra as transações por ID da categoria",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filtra as transações por ID do usuário",
     *         required=false,
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtra as transações pelo tipo (income ou expense)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"income", "expense"}, example="income")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de transações retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Listar transações"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Transaction"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::query();

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('id', 'DESC')->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Listar transações',
            'data' => $transactions,
        ]);
    }

    /**
     * Exibe os detalhes de uma transação específica.
     *
     * @OA\Get(
     *     path="/api/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Obtém detalhes de uma transação",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da transação",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da transação retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction")
     *         )
     *     )
     * )
     */
    public function show(Transaction $transaction): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Cria uma nova transação.
     *
     * @OA\Post(
     *     path="/api/transactions",
     *     tags={"Transactions"},
     *     summary="Registra uma nova transação",
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="category_id", type="integer", example=3),
     *             @OA\Property(property="amount", type="number", format="float", example=150.75),
     *             @OA\Property(property="description", type="string", example="Pagamento de serviço"),
     *             @OA\Property(property="type", type="string", enum={"income", "expense"}, example="income")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transação criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transação criada com sucesso!"),
     *             @OA\Property(property="transaction", ref="#/components/schemas/Transaction")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:income,expense',
        ]);

        $transaction = Transaction::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Transação criada com sucesso!',
            'transaction' => $transaction,
        ], 201);
    }

    /**
     * Atualiza uma transação existente.
     *
     * @OA\Put(
     *     path="/api/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Atualiza os detalhes de uma transação",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da transação a ser atualizada",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", format="float", example=200.00),
     *             @OA\Property(property="type", type="string", enum={"income", "expense"}, example="expense")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transação atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transação atualizada com sucesso!"),
     *             @OA\Property(property="data", ref="#/components/schemas/Transaction")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'category_id' => 'sometimes|exists:categories,id',
            'type' => 'sometimes|in:income,expense', 
            'amount' => 'sometimes|numeric|min:0.01',
        ]);

        $transaction->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Transação atualizada com sucesso!',
            'data' => $transaction,
        ], 200);
    }

    /**
     * Remove uma transação existente.
     *
     * @OA\Delete(
     *     path="/api/transactions/{id}",
     *     tags={"Transactions"},
     *     summary="Deleta uma transação",
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da transação a ser deletada",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Transação deletada com sucesso"
     *     )
     * )
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json([
            'status' => true,
            'message' => 'Transação deletada com sucesso!',
        ], 204);
    }
}
