<?php

namespace App\Repositories\Interfaces;

interface ResetMemoryRepositoryInterface {
    /**
     * Delete all stored accounts
     *
     * @return void
     */
    public function resetMemory(): bool;
}
