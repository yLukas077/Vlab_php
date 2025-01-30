<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /**
     * @OA\Schema(
     *     schema="Transaction",
     *     type="object",
     *     title="Transaction",
     *     description="Transaction model",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="user_id", type="integer", example=1),
     *     @OA\Property(property="category_id", type="integer", example=2),
     *     @OA\Property(property="type", type="string", enum={"income", "expense"}),
     *     @OA\Property(property="amount", type="number", format="float", example=150.75),
     *     @OA\Property(property="created_at", type="string", format="date-time"),
     *     @OA\Property(property="updated_at", type="string", format="date-time"),
     * )
     */

    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'amount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
