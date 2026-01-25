<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\ReplyTicketResponse;
use PHPUnit\Framework\TestCase;

class ReplyTicketResponseTest extends TestCase
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

    public function testSuccess()
    {
        $httpResponse = new Response(200, [], 'SUCCESS');

        $replyTicketResponse = new ReplyTicketResponse($httpResponse);

        $this->assertTrue($replyTicketResponse->isSuccessful());
        $this->assertNull($replyTicketResponse->getMessage());
    }

    public function testError()
    {
        $message = 'ipaddress post field was empty';

        $httpResponse = new Response(200, [], "\n{$message}");

        $replyTicketResponse = new ReplyTicketResponse($httpResponse);

        $this->assertFalse($replyTicketResponse->isSuccessful());
        $this->assertEquals($message, $replyTicketResponse->getMessage());
    }
}
