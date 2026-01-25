<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\CreateTicketResponse;
use PHPUnit\Framework\TestCase;

class CreateTicketResponseTest extends TestCase
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
        $ticketId = $this->faker->numerify('######');

        $httpResponse = new Response(200, [], "SUCCESS:{$ticketId}");

        $createTicketResponse = new CreateTicketResponse($httpResponse);

        $this->assertTrue($createTicketResponse->isSuccessful());
        $this->assertEquals($ticketId, $createTicketResponse->getTicketId());
        $this->assertNull($createTicketResponse->getMessage());
    }

    public function testError()
    {
        $message = 'the username posted does not appear to be owned by this reseller api key pair';

        $httpResponse = new Response(200, [], "\n\n{$message}\n");

        $createTicketResponse = new CreateTicketResponse($httpResponse);

        $this->assertFalse($createTicketResponse->isSuccessful());
        $this->assertNull($createTicketResponse->getTicketId());
        $this->assertEquals($message, $createTicketResponse->getMessage());
    }
}
