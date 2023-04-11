<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Service\Message;
use Service\TestInterface;
use Spiral\Goridge\Frame;
use Spiral\Goridge\RelayInterface;
use Spiral\RoadRunner\GRPC\Server;
use Spiral\RoadRunner\GRPC\Tests\Stub\TestService;
use Spiral\RoadRunner\Worker;

class ServerTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = new Server();
        $this->server->registerService(TestInterface::class, new TestService());
    }

    public function testInvoke(): void
    {
        $relay = $this->createRelay(
            'ping',
            [
                'service' => 'service.Test',
                'method' => 'Echo',
                'context' => [],
            ]
        );

        $relay->shouldReceive('send')->once()->withArgs(function (Frame $frame) {
            return $frame->payload === '{}'.$this->packMessage('pong');
        });

        $this->server->serve(
            new Worker($relay)
        );
    }

    public function testNotFound(): void
    {
        $relay = $this->createRelay(
            'ping',
            [
                'service' => 'service.Test2',
                'method' => 'Echo',
                'context' => [],
            ]
        );

        $relay->shouldReceive('send')->once()->withArgs(function (Frame $frame) {
            $error = base64_decode(json_decode($frame->payload, true)['error']);

            return str_contains($error, 'Service `service.Test2` not found.');
        });

        $this->server->serve(
            new Worker($relay)
        );
    }

    public function testNotFound2(): void
    {
        $relay = $this->createRelay(
            'ping',
            [
                'service' => 'service.Test',
                'method' => 'Echo2',
                'context' => [],
            ]
        );

        $relay->shouldReceive('send')->once()->withArgs(function (Frame $frame) {
            $error = base64_decode(json_decode($frame->payload, true)['error']);

            return str_contains($error, 'Method `Echo2` not found in service `service.Test`.');
        });

        $this->server->serve(
            new Worker($relay)
        );
    }

    public function testServerDebugModeNotEnabled(): void
    {
        $relay = $this->createRelay(
            'regularException',
            [
                'service' => 'service.Test',
                'method' => 'Throw',
                'context' => [],
            ]
        );

        $relay->shouldReceive('send')->once()->withArgs(function (Frame $frame) {
            return $frame->payload === 'Just another exception';
        });

        $this->server->serve(
            new Worker($relay)
        );
    }

    private function packMessage(string $message): string
    {
        $m = new Message();
        $m->setMsg($message);

        return $m->serializeToString();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        ob_end_clean();

        m::close();
    }

    protected function createRelay(string $body, array $header): RelayInterface
    {
        $body = $this->packMessage($body);
        $header = json_encode($header);

        $relay = m::mock(RelayInterface::class);
        $relay->shouldReceive('waitFrame')->once()->andReturn(
            new Frame($header.$body, [mb_strlen($header)])
        );

        $header = json_encode(['stop' => true]);
        $relay->shouldReceive('waitFrame')->once()->andReturn(
            new Frame($header, [mb_strlen($header)], Frame::CONTROL)
        );

        return $relay;
    }
}
