<?php

use PHPUnit\Framework\TestCase;
use App\Repositories\Interfaces\ResetMemoryRepositoryInterface;
use App\Services\Interfaces\ResetMemoryServiceInterface;
use App\Services\MemoryService;

class MemoryServiceTest extends TestCase
{
    private ResetMemoryServiceInterface $resetMemoryService;
    private $memoryRepositoryMock;

    protected function setUp(): void
    {
        $this->memoryRepositoryMock = $this->createMock(ResetMemoryRepositoryInterface::class);
        $this->resetMemoryService = new MemoryService($this->memoryRepositoryMock);
    }

    public function testResetMemorySuccess() {
        $this->memoryRepositoryMock
            ->expects($this->once())
            ->method('resetMemory')
            ->willReturn(true);

        $result = $this->resetMemoryService->resetMemory();
        $this->assertTrue($result, "Memory should be reset successfully.");
    }
}
