<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function find(int $id): Product;

    public function findWithRelations(int $id, array $relations): Product;

    public function getForUser(User $user): Collection;

    public function getStatsForUser(User $user): array;

    public function createWithLinks(User $user, string $name, array $urls): Product;
}
