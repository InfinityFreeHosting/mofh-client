<?php

namespace InfinityFree\MofhClient\Tests\Message;

use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\AvailabilityResponse;
use PHPUnit\Framework\TestCase;

class AvailabilityResponseTest extends TestCase
{
    public function testAvailableDomain()
    {
        $httpResponse = new Response(200, [], '1');

        $availabilityResponse = new AvailabilityResponse($httpResponse);

        $this->assertTrue($availabilityResponse->isSuccessful());
        $this->assertTrue($availabilityResponse->isAvailable());
        $this->assertNull($availabilityResponse->getMessage());
    }

    public function testNotAvailableDomain()
    {
        $httpResponse = new Response(200, [], '0');

        $availabilityResponse = new AvailabilityResponse($httpResponse);

        $this->assertTrue($availabilityResponse->isSuccessful());
        $this->assertFalse($availabilityResponse->isAvailable());
        $this->assertNull($availabilityResponse->getMessage());
    }

    public function testError()
    {
        $message = 'ERROR :Illegal charachters in domain name . . This domain name does not appear to be valid (to short !). .';

        $httpResponse = new Response(200, [], $message);

        $availabilityResponse = new AvailabilityResponse($httpResponse);

        $this->assertFalse($availabilityResponse->isSuccessful());
        $this->assertFalse($availabilityResponse->isAvailable());
        $this->assertEquals($message, $availabilityResponse->getMessage());
    }
}
