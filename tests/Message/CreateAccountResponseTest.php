<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\CreateAccountResponse;
use PHPUnit\Framework\TestCase;

class CreateAccountResponseTest extends TestCase
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

    public function testSuccessful()
    {
        $username = $this->faker->bothify('????_########');

        $httpResponse = new Response(200, [], "
<createacct>
     <result>
          <options>
                  <ip>n</ip>
		  <vpusername>{$username}</vpusername>
                  <nameserver>ns1.byet.org</nameserver>
                  <nameserver2>ns2.byet.org</nameserver2>
                  <nameserver3/>
                  <nameserver4/>
                  <nameservera/>
                  <nameservera2/>
                  <nameservera3/>
                  <nameservera4/>
                  <nameserverentry/>
                  <nameserverentry2/>
                  <nameserverentry3/>
                  <nameserverentry4/>
                  <package></package>
           </options>
           <rawout>
	   account added to queue to be added
            </rawout>
            <status>1</status>
            <statusmsg>This account has been successfuly created</statusmsg>
     </result>
</createacct>
        ");

        $createAccountResponse = new CreateAccountResponse($httpResponse);
        $this->assertTrue($createAccountResponse->isSuccessful());
        $this->assertEquals($username, $createAccountResponse->getVpUsername());
        $this->assertNull($createAccountResponse->getMessage());
    }

    public function testError()
    {
        $httpResponse = new Response(200, [], '
<createacct>
     <result>
          <options>
                  <ip>n</ip>
                  <nameserver>ns1.byet.org</nameserver>
                  <nameserver2>ns2.byet.org</nameserver2>
                  <nameserver3/>
                  <nameserver4/>
                  <nameservera/>
                  <nameservera2/>
                  <nameservera3/>
                  <nameservera4/>
                  <nameserverentry/>
                  <nameserverentry2/>
                  <nameserverentry3/>
                  <nameserverentry4/>
                  <package></package>
           </options>
           <rawout>
           account added to queue to be added
            </rawout>
            <status>0</status>
            <statusmsg>The API username you are using appears to be invalid 1 (0). .  The API username you are using appears to be invalid 2. </statusmsg>
     </result>
</createacct>
        ');

        $createAccountResponse = new CreateAccountResponse($httpResponse);

        $this->assertFalse($createAccountResponse->isSuccessful());
        $this->assertEquals('The API username you are using appears to be invalid 1 (0). .  The API username you are using appears to be invalid 2.', $createAccountResponse->getMessage());
        $this->assertNull($createAccountResponse->getVpUsername());
    }

    public function testDuplicateXmlDocument()
    {
        $httpResponse = new Response(200, [], '
<createacct>
     <result>
          <options>
                  <ip>n</ip>
                  <nameserver>ns1.byet.org</nameserver>
                  <nameserver2>ns2.byet.org</nameserver2>
                  <nameserver3/>
                  <nameserver4/>
                  <nameservera/>
                  <nameservera2/>
                  <nameservera3/>
                  <nameservera4/>
                  <nameserverentry/>
                  <nameserverentry2/>
                  <nameserverentry3/>
                  <nameserverentry4/>
                  <package></package>
           </options>
           <rawout>
           account added to queue to be added
            </rawout>
            <status>0</status>
            <statusmsg>Name servers for domain are not valid 84572435 </statusmsg>
     </result>
</createacct><createacct>
     <result>
          <options>
                  <ip>n</ip>
                  <nameserver>ns1.byet.org</nameserver>
                  <nameserver2>ns2.byet.org</nameserver2>
                  <nameserver3/>
                  <nameserver4/>
                  <nameservera/>
                  <nameservera2/>
                  <nameservera3/>
                  <nameservera4/>
                  <nameserverentry/>
                  <nameserverentry2/>
                  <nameserverentry3/>
                  <nameserverentry4/>
                  <package></package>
           </options>
           <rawout>
           account added to queue to be added
            </rawout>
            <status>0</status>
            <statusmsg>Name servers for domain are not valid 84572435 The name servers for example.com are not set to valid name servers.</statusmsg>
     </result>
</createacct>
        ');

        $createAccountResponse = new CreateAccountResponse($httpResponse);

        $this->assertFalse($createAccountResponse->isSuccessful());
        $this->assertNull($createAccountResponse->getVpUsername());
    }
}
