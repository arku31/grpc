<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;
use Spiral\RoadRunner\GRPC\Exception\InvokeException;
use Spiral\RoadRunner\GRPC\Exception\NotFoundException;
use Spiral\RoadRunner\GRPC\Exception\UnauthenticatedException;
use Spiral\RoadRunner\GRPC\Exception\UnimplementedException;
use Spiral\RoadRunner\GRPC\StatusCode;

class ExceptionTest extends TestCase
{
    public function testDefault(): void
    {
        $e = new GRPCException();
        $this->assertSame(StatusCode::UNKNOWN, $e->getCode());
    }

    public function testNotFound(): void
    {
        $e = new NotFoundException();
        $this->assertSame(StatusCode::NOT_FOUND, $e->getCode());
    }

    public function testInvoke(): void
    {
        $e = new InvokeException();
        $this->assertSame(StatusCode::UNAVAILABLE, $e->getCode());
    }

    public function testUnauthenticated(): void
    {
        $e = new UnauthenticatedException();
        $this->assertSame(StatusCode::UNAUTHENTICATED, $e->getCode());
    }

    public function testUnimplemented(): void
    {
        $e = new UnimplementedException();
        $this->assertSame(StatusCode::UNIMPLEMENTED, $e->getCode());
    }
}
