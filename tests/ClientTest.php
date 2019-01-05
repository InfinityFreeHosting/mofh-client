<?php

namespace HansAdema\MofhClient\Tests;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use HansAdema\MofhClient\Client;
use HansAdema\MofhClient\Exception;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var Generator
     */
    public $faker;

    /**
     * @var MockHandler The class responsible for sending dummy API responses through the client.
     */
    public $mockHandler;

    /**
     * @var array The list of requests sent to the API.
     */
    public $httpHistory;

    public function setUp()
    {
        $this->faker = FakerFactory::create();

        $this->mockHandler = new MockHandler();
        $handler = HandlerStack::create($this->mockHandler);

        $this->httpHistory = [];
        $handler->push(Middleware::history($this->httpHistory));

        $this->client = new Client($this->faker->userName, $this->faker->password, $this->faker->url, new \GuzzleHttp\Client([
            'handler' => $handler,
        ]));
    }

    public function testConstruct()
    {
        $username = $this->faker->userName;
        $password = $this->faker->password;
        $url = $this->faker->url;

        $client = new Client($username, $password, $url);

        $property = new \ReflectionProperty(Client::class, 'apiUsername');
        $property->setAccessible(true);
        $this->assertEquals($username, $property->getValue($client));

        $property = new \ReflectionProperty(Client::class, 'apiPassword');
        $property->setAccessible(true);
        $this->assertEquals($password, $property->getValue($client));

        $property = new \ReflectionProperty(Client::class, 'httpClient');
        $property->setAccessible(true);
        $guzzle = $property->getValue($client);
        $this->assertEquals($url, $guzzle->getConfig('base_uri'));
    }

    public function testCreateacct()
    {
        $username = $this->faker->userName;
        $password = $this->faker->password;
        $email = $this->faker->email;
        $domain = $this->faker->domainName;
        $plan = $this->faker->word;

        $this->mockHandler->append(new Response(200, [], '<createacct>
     <result>        
          <options>
                  <ip>n</ip>
		  <vpusername>test_12345678</vpusername>
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
</createacct>'));

        $result = $this->client->createacct($username, $password, $email, $domain, $plan);

        $this->assertEquals('test_12345678', $result);
        $this->assertEquals(1, count($this->httpHistory));
        $request = $this->httpHistory[0]['request'];

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('createacct', $request->getUri());

        $body = [];
        parse_str($request->getBody(), $body);
        $this->assertEquals([
            'username' => $username,
            'password' => $password,
            'contactemail' => $email,
            'domain' => $domain,
            'plan' => $plan,
        ], $body);
    }

    public function testCreateacctFail()
    {
        $password = $this->faker->password;
        $email = $this->faker->email;
        $domain = $this->faker->domainName;
        $plan = $this->faker->word;

        $this->mockHandler->append(new Response(200, [], '<createacct>
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
            <statusmsg>The username test1234 appears to be allready created.  .  </statusmsg>
     </result>
</createacct>'));

        try {
            $this->client->createacct('test1234', $password, $email, $domain, $plan);

            $this->fail('An exception should have been thrown here');
        } catch (Exception $e) {
            $this->assertEquals('The username test1234 appears to be allready created.  .', $e->getMessage());
        }
    }

    public function testSuspendacct()
    {
        $username = $this->faker->userName;
        $reason = $this->faker->sentence;

        $this->mockHandler->append(new Response(200, [], "<suspendacct>
    <result>
        <status>1</status>
        <statusmsg>
            <script>if (self['clear_ui_status']) { clear_ui_status(); }</script>
            Changing Shell to /bin/false...Changing shell for test1234.
            Shell changed.
            Locking Password...Locking password for user test1234.
            marking user vhosts / databases for suspension.
	    ..
	    ..
	    Completed, this account will be fully suspended in 2 minutes.
        </statusmsg>
    </result>
</suspendacct>"));

        $this->client->suspendacct($username, $reason);

        $this->assertEquals(1, count($this->httpHistory));
        $request = $this->httpHistory[0]['request'];

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('suspendacct', $request->getUri());

        $body = [];
        parse_str($request->getBody(), $body);
        $this->assertEquals([
            'user' => $username,
            'reason' => $reason,
        ], $body);
    }

    public function testSuspendacctFail()
    {
        $username = $this->faker->userName;
        $reason = $this->faker->sentence;

        $this->mockHandler->append(new Response(200, [], "The suspension reason is to short, please give a reason for suspension.  .  "));

        try {
            $this->client->suspendacct($username, $reason);

            $this->fail('An exception should have been thrown.');
        } catch (Exception $e) {
            $this->assertEquals('The suspension reason is to short, please give a reason for suspension.  .', $e->getMessage());
        }
    }

    public function testUnsuspendacct()
    {
        $username = $this->faker->userName;

        $this->mockHandler->append(new Response(200, [], '
<unsuspendacct>
    <result>
        <status>1</status>
        <statusmsg>
            <script>if (self[\'clear_ui_status\']) { clear_ui_status(); }</script>
            test1234 account has been unsuspended
        </statusmsg>
    </result>
</unsuspendacct>
'));

        $this->client->unsuspendacct($username);

        $this->assertEquals(1, count($this->httpHistory));
        $request = $this->httpHistory[0]['request'];

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('unsuspendacct', $request->getUri());

        $body = [];
        parse_str($request->getBody(), $body);
        $this->assertEquals([
            'user' => $username,
        ], $body);
    }

    public function testUnsuspendacctFail()
    {
        $username = $this->faker->userName;

        $this->mockHandler->append(new Response(200, [], 'The username is invalid (8 characters maximum). .   '));

        try {
            $this->client->unsuspendacct($username);
        } catch (Exception $e) {
            $this->assertEquals('The username is invalid (8 characters maximum). .', $e->getMessage());
        }
    }

    public function testPasswd()
    {
        $username = $this->faker->userName;
        $password = $this->faker->password;

        $this->mockHandler->append(new Response(200, [], '
<passwd>
        <passwd>
                <rawout>        
                 Changing password for test1234
                 Password for test1234 has been changed 
                 Updating ftp passwords for test1234
                 Ftp password files updated. 
                 Ftp vhost passwords synced
                </rawout>
                 <services>
                      <app>system</app>
                 </services>
                 <services>
                      <app>ftp</app>
                 </services>
                 <services>
                      <app>mail</app>
                 </services>
                 <services>
                       <app>mySQL</app>
                 </services>
                <status>1</status>
                <statusmsg>Password changed for user test1234</statusmsg>
        </passwd>
</passwd>
	'));

        $this->client->passwd($username, $password);

        $this->assertEquals(1, count($this->httpHistory));
        $request = $this->httpHistory[0]['request'];

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('passwd', $request->getUri());

        $body = [];
        parse_str($request->getBody(), $body);
        $this->assertEquals([
            'user' => $username,
            'pass' => $password,
        ], $body);
    }

    public function testPasswdFail()
    {
        $username = $this->faker->userName;
        $password = $this->faker->password;

        $this->mockHandler->append(new Response(200, [], '<passwd>
        <passwd>
                <rawout>
                 Changing password for vns6ohth
                 Password for vns6ohth has been changed
                 Updating ftp passwords for vns6ohth
                 Ftp password files updated.
                 Ftp vhost passwords synced
                </rawout>
                 <services>
                      <app>system</app>
                 </services>
                 <services>
                      <app>ftp</app>
                 </services>
                 <services>
                      <app>mail</app>
                 </services>
                 <services>
                       <app>mySQL</app>
                 </services>
                <status>0</status>
                <statusmsg>An error occured changing this password.</statusmsg>
        </passwd>
</passwd>'));

        try {
            $this->client->passwd($username, $password);
        } catch (Exception $e) {
            $this->assertEquals('An error occured changing this password.', $e->getMessage());
        }
    }

    public function testCheckavailable()
    {
        $domain = $this->faker->domainName;

        $this->mockHandler->append(new Response(200, [], '1'));

        $this->assertTrue($this->client->checkavailable($domain));

        $this->assertEquals(1, count($this->httpHistory));
        $request = $this->httpHistory[0]['request'];

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('checkavailable', $request->getUri()->getPath());

        $body = [];
        parse_str($request->getUri()->getQuery(), $body);
        $this->assertEquals(['api_user', 'api_key', 'domain'], array_keys($body));
        $this->assertEquals($domain, $body['domain']);
    }

    public function testCheckavailableNotAvailable()
    {
        $domain = $this->faker->domainName;

        $this->mockHandler->append(new Response(200, [], '0'));

        $this->assertFalse($this->client->checkavailable($domain));
    }

    public function testCheckavailableFail()
    {
        $domain = $this->faker->domainName;

        $this->mockHandler->append(new Response(200, [], 'ERROR :The API username you are using appears to be invalid 1. .  The API key you are using appears to be invalid 1.  .'));

        try {
            $this->client->checkavailable($domain);
        } catch (Exception $e) {
            $this->assertEquals('ERROR :The API username you are using appears to be invalid 1. .  The API key you are using appears to be invalid 1.  .', $e->getMessage());
        }
    }

    public function testGetuserdomains()
    {
        $username = $this->faker->word . '_' . $this->faker->randomNumber;
        $domain1 = $this->faker->domainName;
        $domain2 = $this->faker->domainName;

        $response = [
            ["ACTIVE", $domain1],
            ["SUSPENDED", $domain2],
        ];

        $this->mockHandler->append(new Response(200, [], json_encode($response)));

        $this->assertEquals($response, $this->client->getuserdomains($username));

        $this->assertEquals(1, count($this->httpHistory));
        $request = $this->httpHistory[0]['request'];

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('getuserdomains', $request->getUri()->getPath());

        $body = [];
        parse_str($request->getUri()->getQuery(), $body);
        $this->assertEquals(['api_user', 'api_key', 'username'], array_keys($body));
        $this->assertEquals($username, $body['username']);
    }

    public function testGetuserdomainsEmpty()
    {
        $username = $this->faker->word . '_' . $this->faker->randomNumber;

        $this->mockHandler->append(new Response(200, [], json_encode([])));

        $this->assertEquals([], $this->client->getuserdomains($username));
    }

    public function testGetuserdomainsNull()
    {
        $username = $this->faker->word . '_' . $this->faker->randomNumber;

        $this->mockHandler->append(new Response(200, [], "null"));

        $this->assertEquals([], $this->client->getuserdomains($username));
    }
}
