<?php

namespace App\Services;

use App\Repositories\Interfaces\ResetMemoryRepositoryInterface;
use App\Services\Interfaces\ResetMemoryServiceInterface;

class MemoryService implements ResetMemoryServiceInterface {

    private readonly ResetMemoryRepositoryInterface $resetMemoryRepository;

    public function __construct(ResetMemoryRepositoryInterface $resetMemoryRepository)
    {
        $this->resetMemoryRepository = $resetMemoryRepository;
    }

    /**
     * Delete all stored accounts
     *
     * @return bool
     */
    public function resetMemory(): bool
    {
        return $this->resetMemoryRepository->resetMemory();
    }

}
