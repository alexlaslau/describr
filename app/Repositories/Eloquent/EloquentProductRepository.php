<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\ProductLink;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function find(int $id): Product
    {
        return Product::findOrFail($id);
    }

    public function findWithRelations(int $id, array $relations): Product
    {
        return Product::with($relations)->findOrFail($id);
    }

    public function getForUser(User $user): Collection
    {
        return Product::query()
            ->where('user_id', $user->id)
            ->withCount('productLinks')
            ->latest()
            ->get();
    }

    public function getStatsForUser(User $user): array
    {
        return Cache::remember("user:{$user->id}:product-stats", now()->addMinutes(5), function () use ($user): array {
            $stats = Product::query()
                ->where('user_id', $user->id)
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status NOT IN ('completed', 'failed', 'pending') THEN 1 ELSE 0 END) as in_progress
                ")
                ->first();

            return [
                'total'      => (int) $stats->total,
                'completed'  => (int) $stats->completed,
                'inProgress' => (int) $stats->in_progress,
            ];
        });
    }

    public function createWithLinks(User $user, string $name, array $urls): Product
    {
        $product = $user->products()->create([
            'name'   => $name,
            'status' => 'pending',
        ]);

        $links = array_map(fn (string $url) => [
            'url'    => $url,
            'status' => 'pending',
        ], $urls);

        $product->productLinks()->createMany($links);

        return $product;
    }
}
