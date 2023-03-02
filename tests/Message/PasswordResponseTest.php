<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\PasswordResponse;
use PHPUnit\Framework\TestCase;

class PasswordResponseTest extends TestCase
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
        $shortUsername = $this->faker->lexify('????????');

        $httpResponse = new Response(200, [], "
<passwd>
        <passwd>
                <rawout>
                 Changing password for {$shortUsername}
                 Password for {$shortUsername} has been changed
                 Updating ftp passwords for {$shortUsername}
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
                <statusmsg>Password changed for user {$shortUsername}</statusmsg>
        </passwd>
</passwd>
        ");

        $passwordResponse = new PasswordResponse($httpResponse);

        $this->assertTrue($passwordResponse->isSuccessful());
        $this->assertEquals('a', $passwordResponse->getStatus());
        $this->assertNull($passwordResponse->getMessage());
    }

    public function testPasswordIdentical()
    {
        $shortUsername = $this->faker->lexify('????????');

        $httpResponse = new Response(200, [], "
<passwd>
        <passwd>
                <rawout>
                 Changing password for {$shortUsername}
                 Password for {$shortUsername} has been changed
                 Updating ftp passwords for {$shortUsername}
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
</passwd>
        ");

        $passwordResponse = new PasswordResponse($httpResponse);

        $this->assertTrue($passwordResponse->isSuccessful());
        $this->assertEquals('a', $passwordResponse->getStatus());
        $this->assertNull($passwordResponse->getMessage());
    }

    public function testAccountNotActive()
    {
        $shortUsername = $this->faker->lexify('????????');

        $httpResponse = new Response(200, [], "
<passwd>
        <passwd>
                <rawout>
                 Changing password for {$shortUsername}
                 Password for {$shortUsername} has been changed
                 Updating ftp passwords for {$shortUsername}
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
                <statusmsg>This account  currently not active, the account must be active to change the password (x). .   </statusmsg>
        </passwd>
</passwd>
        ");

        $passwordResponse = new PasswordResponse($httpResponse);

        $this->assertFalse($passwordResponse->isSuccessful());
        $this->assertEquals('x', $passwordResponse->getStatus());
        $this->assertEquals(
            'This account  currently not active, the account must be active to change the password (x). .',
            $passwordResponse->getMessage()
        );
    }

    public function testError()
    {
        $httpResponse = new Response(200, [], 'The API username you are using appears to be invalid 1. .  ');

        $passwordResponse = new PasswordResponse($httpResponse);

        $this->assertFalse($passwordResponse->isSuccessful());
        $this->assertNull($passwordResponse->getStatus());
        $this->assertEquals('The API username you are using appears to be invalid 1. .', $passwordResponse->getMessage());
    }
}
