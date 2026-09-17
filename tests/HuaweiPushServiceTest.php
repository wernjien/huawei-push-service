<?php

namespace Innoractive\HuaweiPushService\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Innoractive\HuaweiPushService\HuaweiPushService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class HuaweiPushServiceTest extends TestCase
{
    /**
     * Builds a Guzzle client backed by a mock handler, and records every
     * request that passes through it into $history.
     */
    private function clientWithHistory(array $responses, array &$history): Client
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($history));

        return new Client(['handler' => $stack]);
    }

    public function testGetAccessTokenReturnsTokenFromResponse(): void
    {
        $history = [];
        $client = $this->clientWithHistory([
            new Response(200, [], json_encode(['access_token' => 'abc123', 'expires_in' => 3600])),
        ], $history);

        $token = HuaweiPushService::getAccessToken('client-id', 'client-secret', $client);

        $this->assertSame('abc123', $token);
    }

    public function testGetAccessTokenReturnsEmptyStringWhenTokenMissing(): void
    {
        $history = [];
        $client = $this->clientWithHistory([
            new Response(200, [], json_encode(['error' => 'invalid_client'])),
        ], $history);

        $token = HuaweiPushService::getAccessToken('client-id', 'client-secret', $client);

        $this->assertSame('', $token);
    }

    public function testGetAccessTokenSendsExpectedRequest(): void
    {
        $history = [];
        $client = $this->clientWithHistory([
            new Response(200, [], json_encode(['access_token' => 'abc123'])),
        ], $history);

        HuaweiPushService::getAccessToken('my-client-id', 'secret&with=special+chars', $client);

        $this->assertCount(1, $history);

        $request = $history[0]['request'];

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://oauth-login.cloud.huawei.com/oauth2/v3/token', (string) $request->getUri());
        $this->assertStringContainsString('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));

        parse_str((string) $request->getBody(), $body);

        $this->assertSame('client_credentials', $body['grant_type']);
        $this->assertSame('my-client-id', $body['client_id']);
        $this->assertSame('secret&with=special+chars', $body['client_secret']);
    }

    public function testSendNotificationThrowsWhenTokensAreEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        HuaweiPushService::sendNotification('client-id', 'access-token', 'title', 'body', []);
    }

    public function testSendNotificationWrapsSingleTokenIntoArray(): void
    {
        $history = [];
        $client = $this->clientWithHistory([
            new Response(200, [], json_encode(['code' => '80000000', 'msg' => 'Success'])),
        ], $history);

        HuaweiPushService::sendNotification('client-id', 'access-token', 'title', 'body', 'single-token', client: $client);

        $payload = json_decode((string) $history[0]['request']->getBody(), true);

        $this->assertSame(['single-token'], $payload['message']['token']);
    }

    public function testSendNotificationSendsExpectedRequest(): void
    {
        $history = [];
        $client = $this->clientWithHistory([
            new Response(200, [], json_encode(['code' => '80000000', 'msg' => 'Success'])),
        ], $history);

        HuaweiPushService::sendNotification(
            'my client/id',
            'my-access-token',
            'Hello',
            'World',
            ['token-a', 'token-b'],
            ['type' => 2, 'url' => 'https://example.com'],
            $client
        );

        $this->assertCount(1, $history);

        $request = $history[0]['request'];

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(
            'https://push-api.cloud.huawei.com/v1/my%20client%2Fid/messages:send',
            (string) $request->getUri()
        );
        $this->assertSame('Bearer my-access-token', $request->getHeaderLine('Authorization'));
        $this->assertSame('application/json', $request->getHeaderLine('Accept'));
        $this->assertStringContainsString('application/json', $request->getHeaderLine('Content-Type'));

        $payload = json_decode((string) $request->getBody(), true);

        $this->assertFalse($payload['validate_only']);
        $this->assertSame('Hello', $payload['message']['notification']['title']);
        $this->assertSame('World', $payload['message']['notification']['body']);
        $this->assertSame(['token-a', 'token-b'], $payload['message']['token']);
        $this->assertSame(
            ['type' => 2, 'url' => 'https://example.com'],
            $payload['message']['android']['notification']['click_action']
        );
    }

    public function testSendNotificationDefaultsClickActionToIgnore(): void
    {
        $history = [];
        $client = $this->clientWithHistory([
            new Response(200, [], json_encode(['code' => '80000000'])),
        ], $history);

        HuaweiPushService::sendNotification('client-id', 'access-token', 'title', 'body', 'token', client: $client);

        $payload = json_decode((string) $history[0]['request']->getBody(), true);

        $this->assertSame(['type' => 3], $payload['message']['android']['notification']['click_action']);
    }

    public function testSendNotificationReturnsDecodedResponse(): void
    {
        $history = [];
        $client = $this->clientWithHistory([
            new Response(200, [], json_encode(['code' => '80000000', 'msg' => 'Success', 'requestId' => 'abc'])),
        ], $history);

        $response = HuaweiPushService::sendNotification('client-id', 'access-token', 'title', 'body', 'token', client: $client);

        $this->assertSame('80000000', $response->code);
        $this->assertSame('Success', $response->msg);
        $this->assertSame('abc', $response->requestId);
    }

    public function testSendNotificationReturnsEmptyObjectWhenResponseBodyIsNotJson(): void
    {
        $history = [];
        $client = $this->clientWithHistory([
            new Response(200, [], ''),
        ], $history);

        $response = HuaweiPushService::sendNotification('client-id', 'access-token', 'title', 'body', 'token', client: $client);

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertEquals(new stdClass, $response);
    }
}
