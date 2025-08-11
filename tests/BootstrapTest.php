<?php
use PHPUnit\Framework\TestCase;

final class BootstrapTest extends TestCase
{
    public function testAppBootstrapReturnsAppInstance(): void
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $this->assertIsObject($app);
        $this->assertInstanceOf(\Slim\App::class, $app);
        $this->assertTrue(method_exists($app, 'run'));
    }
}
