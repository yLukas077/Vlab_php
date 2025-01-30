<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /**
     * @OA\Schema(
     *     schema="Category",
     *     type="object",
     *     title="Category",
     *     description="Category model",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Food"),
     *     @OA\Property(property="created_at", type="string", format="date-time"),
     *     @OA\Property(property="updated_at", type="string", format="date-time"),
     * )
     */

    use HasFactory;

    protected $fillable = ['name'];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
