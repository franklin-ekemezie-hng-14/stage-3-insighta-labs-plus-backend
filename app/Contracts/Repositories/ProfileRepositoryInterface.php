<?php
declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DTOs\ProfileData;
use App\Support\PaginatedCollection;


interface ProfileRepositoryInterface
{


    public function findByName(string $name): ?ProfileData;

    public function findById(string $id): ?ProfileData;

    public function getAll(int $limit, int $page=1, array $filters=[]): PaginatedCollection;

    public function count(): int;

    public function create(array $data): ProfileData;

    public function delete(string $id): bool;

}
