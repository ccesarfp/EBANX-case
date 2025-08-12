<?php

namespace App\Services\Interfaces;

interface ResetMemoryServiceInterface {
    /**
     * Delete all stored accounts
     *
     * @return void
     */
    public function resetMemory(): bool;
}
