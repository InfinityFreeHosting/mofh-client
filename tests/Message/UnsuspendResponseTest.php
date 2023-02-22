<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\UnsuspendResponse;
use PHPUnit\Framework\TestCase;

class UnsuspendResponseTest extends TestCase
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
        $httpResponse = new Response(200, [], "
<unsuspendacct>
    <result>
        <status>1</status>
        <statusmsg>
            <script>if (self['clear_ui_status']) { clear_ui_status(); }</script>
            abcd1234 account has been unsuspended
        </statusmsg>
    </result>
</unsuspendacct>
        ");

        $unsuspendResponse = new UnsuspendResponse($httpResponse);

        $this->assertTrue($unsuspendResponse->isSuccessful());
        $this->assertNull($unsuspendResponse->getStatus());
        $this->assertNull($unsuspendResponse->getMessage());
    }

    public function testNotSuspended()
    {
        $httpResponse = new Response(200, [], '
<unsuspendacct>
    <result>
        <status>0</status>
        <statusmsg>
	This account is NOT currently suspended (status : r ) .  .
        </statusmsg>
    </result>
</unsuspendacct>
        ');

        $unsuspendResponse = new UnsuspendResponse($httpResponse);

        $this->assertFalse($unsuspendResponse->isSuccessful());
        $this->assertEquals('r', $unsuspendResponse->getStatus());
        $this->assertEquals('This account is NOT currently suspended (status : r ) .  .', $unsuspendResponse->getMessage());
    }

    public function testNotFound()
    {
        $httpResponse = new Response(200, [], '
<unsuspendacct>
    <result>
        <status>0</status>
        <statusmsg>
	This account is NOT currently suspended (status :  ) .  .
        </statusmsg>
    </result>
</unsuspendacct>
        ');

        $unsuspendResponse = new UnsuspendResponse($httpResponse);

        $this->assertFalse($unsuspendResponse->isSuccessful());
        $this->assertEquals('d', $unsuspendResponse->getStatus());
        $this->assertEquals('This account is NOT currently suspended (status :  ) .  .', $unsuspendResponse->getMessage());
    }

    public function testError()
    {
        $httpResponse = new Response(200, [], 'The API username you are using appears to be invalid 1. .  ');

        $unsuspendResponse = new UnsuspendResponse($httpResponse);

        $this->assertFalse($unsuspendResponse->isSuccessful());
        $this->assertNull($unsuspendResponse->getStatus());
        $this->assertEquals('The API username you are using appears to be invalid 1. .', $unsuspendResponse->getMessage());
    }
}
