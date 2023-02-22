<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Exception\MismatchedStatusesException;
use InfinityFree\MofhClient\Message\GetUserDomainsResponse;
use PHPUnit\Framework\TestCase;

class GetUserDomainsResponseTest extends TestCase
{
    /**
     * @var Generator
     */
    public $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    public function testReturnsDomains()
    {
        $domain1 = $this->faker->domainName();
        $domain2 = $this->faker->domainName();

        $httpResponse = new Response(200, [], json_encode([
            ['ACTIVE', $domain1],
            ['ACTIVE', $domain2],
        ]));

        $getUserDomainsResponse = new GetUserDomainsResponse($httpResponse);

        $this->assertTrue($getUserDomainsResponse->isSuccessful());
        $this->assertEquals([$domain1, $domain2], $getUserDomainsResponse->getDomains());
        $this->assertEquals('ACTIVE', $getUserDomainsResponse->getStatus());
        $this->assertNull($getUserDomainsResponse->getMessage());
    }

    public function testNoDomains()
    {
        $httpResponse = new Response(200, [], 'null');

        $getUserDomainsResponse = new GetUserDomainsResponse($httpResponse);

        $this->assertTrue($getUserDomainsResponse->isSuccessful());
        $this->assertEquals([], $getUserDomainsResponse->getDomains());
        $this->assertEquals(null, $getUserDomainsResponse->getStatus());
        $this->assertNull($getUserDomainsResponse->getMessage());
    }

    public function testError()
    {
        $message = 'ERROR :The API key you are using appears to be invalid 1. . The API key you are using appears to be invalid 2. . ';
        $httpResponse = new Response(200, [], $message);

        $getUserDomainsResponse = new GetUserDomainsResponse($httpResponse);

        $this->assertFalse($getUserDomainsResponse->isSuccessful());
        $this->assertEquals([], $getUserDomainsResponse->getDomains());
        $this->assertEquals(null, $getUserDomainsResponse->getStatus());
        $this->assertEquals(trim($message), $getUserDomainsResponse->getMessage());
    }

    public function testMistmachedStatuses()
    {
        $domain1 = $this->faker->domainName();
        $domain2 = $this->faker->domainName();

        $httpResponse = new Response(200, [], json_encode([
            ['ACTIVE', $domain1],
            ['SUSPENDED', $domain2],
        ]));

        $getUserDomainsResponse = new GetUserDomainsResponse($httpResponse);

        $this->assertTrue($getUserDomainsResponse->isSuccessful());

        $this->expectException(MismatchedStatusesException::class);
        $getUserDomainsResponse->getStatus();
    }
}
