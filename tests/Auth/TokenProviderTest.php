<?php

namespace RenokiCo\PhpK8s\Test\Auth;

use RenokiCo\PhpK8s\Auth\TokenProvider;
use RenokiCo\PhpK8s\Test\TestCase;

class TokenProviderTest extends TestCase
{
    public function test_token_not_expired_without_expiration()
    {
        $provider = new class extends TokenProvider
        {
            public function refresh(): void
            {
                $this->token = 'test-token';
                $this->expiresAt = null; // No expiration
            }
        };

        $this->assertFalse($provider->isExpired());
    }

    public function test_token_not_expired_when_far_future()
    {
        $provider = new class extends TokenProvider
        {
            public function refresh(): void
            {
                $this->token = 'test-token';
                $this->expiresAt = (new \DateTimeImmutable)->modify('+1 hour');
            }
        };

        $provider->refresh();
        $this->assertFalse($provider->isExpired());
    }

    public function test_token_expired_when_past()
    {
        $provider = new class extends TokenProvider
        {
            public function refresh(): void
            {
                $this->token = 'test-token';
                $this->expiresAt = (new \DateTimeImmutable)->modify('-1 hour');
            }
        };

        $provider->refresh();
        $this->assertTrue($provider->isExpired());
    }

    public function test_token_expired_within_refresh_buffer()
    {
        $provider = new class extends TokenProvider
        {
            public function refresh(): void
            {
                $this->token = 'test-token';
                // Expires in 30 seconds (within default 60s buffer)
                $this->expiresAt = (new \DateTimeImmutable)->modify('+30 seconds');
            }
        };

        $provider->refresh();
        $this->assertTrue($provider->isExpired()); // Should be considered expired
    }

    public function test_get_token_triggers_refresh_when_expired()
    {
        $refreshCount = 0;

        $provider = new class($refreshCount) extends TokenProvider
        {
            public function __construct(private int &$refreshCountRef) {}

            public function refresh(): void
            {
                $this->refreshCountRef++;
                $this->token = 'refreshed-token-'.$this->refreshCountRef;
                // Set expiration far in future so second call doesn't refresh
                $this->expiresAt = (new \DateTimeImmutable)->modify('+1 hour');
            }
        };

        // First call triggers refresh
        $token1 = $provider->getToken();
        $this->assertEquals(1, $refreshCount);

        // Immediately calling again doesn't refresh (not expired yet)
        $token2 = $provider->getToken();
        $this->assertEquals(1, $refreshCount);
        $this->assertEquals($token1, $token2);
    }

    public function test_custom_refresh_buffer()
    {
        $provider = new class extends TokenProvider
        {
            public function refresh(): void
            {
                $this->token = 'test-token';
                // Expires in 90 seconds
                $this->expiresAt = (new \DateTimeImmutable)->modify('+90 seconds');
            }
        };

        $provider->refresh();

        // With default 60s buffer, not expired
        $this->assertFalse($provider->isExpired());

        // Set buffer to 120s
        $provider->setRefreshBuffer(120);

        // Now it should be considered expired
        $this->assertTrue($provider->isExpired());
    }

    public function test_get_expires_at()
    {
        $provider = new class extends TokenProvider
        {
            public function refresh(): void
            {
                $this->token = 'test-token';
                $this->expiresAt = new \DateTimeImmutable('2099-12-31T23:59:59Z');
            }
        };

        $provider->refresh();
        $expiresAt = $provider->getExpiresAt();

        $this->assertInstanceOf(\DateTimeInterface::class, $expiresAt);
        $this->assertEquals('2099-12-31 23:59:59', $expiresAt->format('Y-m-d H:i:s'));
    }
}
