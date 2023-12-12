<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\GetCnameResponse;
use PHPUnit\Framework\TestCase;

class GetCnameResponseTest extends TestCase
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

    public function testReturnsCnameDomain()
    {
        $cnameRecord = md5(time());
        $httpResponse = new Response(200, [], $cnameRecord);

        $getCnameResponse = new GetCnameResponse($httpResponse);

        $this->assertTrue($getCnameResponse->isSuccessful());
        $this->assertEquals($cnameRecord, $getCnameResponse->getCname());
        $this->assertNull($getCnameResponse->getMessage());
    }

    public function testError()
    {
        $message = 'ERROR :The username specified does not appear to be valid.';

        $httpResponse = new Response(200, [], $message);

        $getCnameResponse = new GetCnameResponse($httpResponse);

        $this->assertFalse($getCnameResponse->isSuccessful());
        $this->assertEquals($message, $getCnameResponse->getMessage());
        $this->assertNull($getCnameResponse->getCname());
    }
}
