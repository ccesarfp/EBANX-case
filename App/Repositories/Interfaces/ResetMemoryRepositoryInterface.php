<?php

namespace App\Repositories\Interfaces;

interface ResetMemoryRepositoryInterface {
    /**
     * Delete all stored accounts
     *
     * @return bool
     */
    public function resetMemory(): bool;
}
