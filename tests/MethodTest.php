<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use PHPUnit\Framework\TestCase;
use Service\Message;
use Spiral\RoadRunner\GRPC\ContextInterface;
use Spiral\RoadRunner\GRPC\Method;
use Spiral\RoadRunner\GRPC\Tests\Stub\TestService;

class MethodTest extends TestCase
{
    public function testInvalidParse(): void
    {
        $this->expectException(\Spiral\RoadRunner\GRPC\Exception\GRPCException::class);

        Method::parse(new \ReflectionMethod($this, 'testInvalidParse'));
    }

    public function testMatch(): void
    {
        $s = new TestService();
        $this->assertTrue(Method::match(new \ReflectionMethod($s, 'Info')));
    }

    public function testNoMatch(): void
    {
        $this->assertFalse(Method::match(new \ReflectionMethod($this, 'tM')));
    }

    public function testNoMatch2(): void
    {
        $this->assertFalse(Method::match(new \ReflectionMethod($this, 'tM2')));
    }

    public function testNoMatch3(): void
    {
        $this->assertFalse(Method::match(new \ReflectionMethod($this, 'tM3')));
    }

    public function testNoMatch4(): void
    {
        $this->assertFalse(Method::match(new \ReflectionMethod($this, 'tM4')));
    }

    public function testNoMatch5(): void
    {
        $this->assertFalse(Method::match(new \ReflectionMethod($this, 'tM5')));
    }

    public function testMethodName(): void
    {
        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Info'));
        $this->assertSame('Info', $m->name);
    }

    public function testMethodInputType(): void
    {
        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Info'));
        $this->assertSame(Message::class, $m->outputType);
    }

    public function testMethodOutputType(): void
    {
        $s = new TestService();
        $m = Method::parse(new \ReflectionMethod($s, 'Info'));
        $this->assertSame(Message::class, $m->outputType);
    }

    public function tM(ContextInterface $context, TestService $input): Message
    {
    }

    public function tM2(ContextInterface $context, Message $input): TestService
    {
    }

    public function tM3(TestService $context, Message $input): TestService
    {
    }

    public function tM4(TestService $context, Message $input): Invalid
    {
    }

    public function tM5(TestService $context, Message $input): void
    {
    }
}
