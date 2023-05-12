<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\SuspendResponse;
use PHPUnit\Framework\TestCase;

class SuspendResponseTest extends TestCase
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
<suspendacct>
    <result>
        <status>1</status>
        <statusmsg>
            <script>if (self['clear_ui_status']) { clear_ui_status(); }</script>
            Changing Shell to /bin/false...Changing shell for utlvigl7.
            Shell changed.
            Locking Password...Locking password for user utlvigl7.
            marking user vhosts / databases for suspension.
	    ..
	    ..
	    Completed, this account will be fully suspended in 2 minutes.
        </statusmsg>
    </result>
</suspendacct>
        ");

        $suspendResponse = new SuspendResponse($httpResponse);

        $this->assertTrue($suspendResponse->isSuccessful());
        $this->assertNull($suspendResponse->getStatus());
        $this->assertNull($suspendResponse->getVpUsername());
        $this->assertNull($suspendResponse->getReason());
        $this->assertNull($suspendResponse->getMessage());
    }

    public function testNotActive()
    {
        $username = $this->faker->bothify('????_########');
        $reason = $this->faker->sentence();

        $httpResponse = new Response(200, [], "
<suspendacct>
    <result>
        <status>0</status>
        <statusmsg>
	This account is not active so can not be suspended  ( vPuser : {$username} ,  status : x , reason : {$reason} ) ..
        </statusmsg>
    </result>
</suspendacct>
        ");

        $suspendResponse = new SuspendResponse($httpResponse);

        $this->assertFalse($suspendResponse->isSuccessful());
        $this->assertEquals('x', $suspendResponse->getStatus());
        $this->assertEquals($username, $suspendResponse->getVpUsername());
        $this->assertEquals($reason, $suspendResponse->getReason());
        $this->assertEquals(
            "This account is not active so can not be suspended  ( vPuser : {$username} ,  status : x , reason : {$reason} ) ..",
            $suspendResponse->getMessage()
        );
    }

    public function testSuspendedWithEmptyReason()
    {
        $username = $this->faker->bothify('????_########');

        $httpResponse = new Response(200, [], "
<suspendacct>
    <result>
        <status>0</status>
        <statusmsg>
	This account is not active so can not be suspended  ( vPuser : {$username} ,  status : x , reason :  ) ..
        </statusmsg>
    </result>
</suspendacct>
        ");

        $suspendResponse = new SuspendResponse($httpResponse);

        $this->assertFalse($suspendResponse->isSuccessful());
        $this->assertEquals('x', $suspendResponse->getStatus());
        $this->assertEquals($username, $suspendResponse->getVpUsername());
        $this->assertEquals('', $suspendResponse->getReason());
        $this->assertEquals(
            "This account is not active so can not be suspended  ( vPuser : {$username} ,  status : x , reason :  ) ..",
            $suspendResponse->getMessage()
        );
    }

    public function testSuspendedWithReasonOnMultipleLines()
    {
        $username = $this->faker->bothify('????_########');

        $reason = $this->faker->sentence." <body>
         <p>
         Hello world!
        </p>
        </body>
         ";
        $encodedReason = htmlspecialchars($reason);

        $httpResponse = new Response(200, [], "
<suspendacct>
    <result>
        <status>0</status>
        <statusmsg>
	This account is not active so can not be suspended  ( vPuser : {$username} ,  status : x , reason : {$encodedReason} ) ..
        </statusmsg>
    </result>
</suspendacct>
        ");

        $suspendResponse = new SuspendResponse($httpResponse);

        $this->assertFalse($suspendResponse->isSuccessful());
        $this->assertEquals('x', $suspendResponse->getStatus());
        $this->assertEquals($username, $suspendResponse->getVpUsername());
        $this->assertEquals(trim($reason), $suspendResponse->getReason());
    }

    public function testError()
    {
        $httpResponse = new Response(200, [], 'The API username you are using appears to be invalid 1. .  ');

        $suspendResponse = new SuspendResponse($httpResponse);

        $this->assertFalse($suspendResponse->isSuccessful());
        $this->assertNull($suspendResponse->getStatus());
        $this->assertNull($suspendResponse->getVpUsername());
        $this->assertNull($suspendResponse->getReason());
        $this->assertEquals('The API username you are using appears to be invalid 1. .', $suspendResponse->getMessage());
    }
}
