<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\RemoveAccountResponse;
use PHPUnit\Framework\TestCase;

class RemoveAccountResponseTest extends TestCase
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
<removeacct>
    <result>
        <status>1</status>
        <statusmsg>
            <script>if (self['clear_ui_status']) { clear_ui_status(); }</script>
            asdf1234 account has been unsuspended
        </statusmsg>
    </result>
</removeacct>
        ");

        $removeAccountResponse = new RemoveAccountResponse($httpResponse);

        $this->assertTrue($removeAccountResponse->isSuccessful());
        $this->assertNull($removeAccountResponse->getMessage());
    }

    public function testError()
    {
        $httpResponse = new Response(200, [], "The username is invalid (Only letters and numbers). .    The username is invalid (8 characters maximum).  .   ");

        $removeAccountResponse = new RemoveAccountResponse($httpResponse);

        $this->assertFalse($removeAccountResponse->isSuccessful());
        $this->assertEquals('The username is invalid (Only letters and numbers). .    The username is invalid (8 characters maximum).  .', $removeAccountResponse->getMessage());
    }
}
