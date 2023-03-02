<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\GetDomainUserResponse;
use PHPUnit\Framework\TestCase;

class GetDomainUserResponseTest extends TestCase
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

    public function testFound()
    {
        $domain = $this->faker->domainName();
        $username = $this->faker->bothify('????_########');
        $documentRoot = "/home/vol12_3/example.com/{$username}/{$domain}/htdocs";

        $httpResponse = new Response(200, [], json_encode(['ACTIVE', $domain, $documentRoot, $username]));

        $getDomainUserResponse = new GetDomainUserResponse($httpResponse);

        $this->assertTrue($getDomainUserResponse->isSuccessful());
        $this->assertTrue($getDomainUserResponse->isFound());
        $this->assertEquals($domain, $getDomainUserResponse->getDomain());
        $this->assertEquals($username, $getDomainUserResponse->getUsername());
        $this->assertEquals($documentRoot, $getDomainUserResponse->getDocumentRoot());
        $this->assertEquals('ACTIVE', $getDomainUserResponse->getStatus());
        $this->assertNull($getDomainUserResponse->getMessage());
    }

    public function testNotFound()
    {
        $httpResponse = new Response(200, [], 'null');

        $getDomainUserResponse = new GetDomainUserResponse($httpResponse);

        $this->assertTrue($getDomainUserResponse->isSuccessful());
        $this->assertFalse($getDomainUserResponse->isFound());
        $this->assertNull($getDomainUserResponse->getDomain());
        $this->assertNull($getDomainUserResponse->getUsername());
        $this->assertNull($getDomainUserResponse->getDocumentRoot());
        $this->assertNull($getDomainUserResponse->getStatus());
        $this->assertNull($getDomainUserResponse->getMessage());
    }

    public function testError()
    {
        $message = 'ERROR :The API key you are using appears to be invalid 1. . The API key you are using appears to be invalid 2. . ';
        $httpResponse = new Response(200, [], $message);

        $getDomainUserResponse = new GetDomainUserResponse($httpResponse);

        $this->assertFalse($getDomainUserResponse->isSuccessful());
        $this->assertFalse($getDomainUserResponse->isFound());
        $this->assertNull($getDomainUserResponse->getDomain());
        $this->assertNull($getDomainUserResponse->getUsername());
        $this->assertNull($getDomainUserResponse->getDocumentRoot());
        $this->assertNull($getDomainUserResponse->getStatus());
        $this->assertEquals(trim($message), $getDomainUserResponse->getMessage());
    }
}
