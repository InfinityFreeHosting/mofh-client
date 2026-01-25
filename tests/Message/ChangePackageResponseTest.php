<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\ChangePackageResponse;
use PHPUnit\Framework\TestCase;

class ChangePackageResponseTest extends TestCase
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
<changepackage>
    <result>
        <rawout>
        </rawout>
        <status>1</status>
        <statusmsg>Account Upgrade/Downgrade Complete for asdfasdf</statusmsg>
    </result>
</changepackage>
        ");

        $changePackageResponse = new ChangePackageResponse($httpResponse);

        $this->assertTrue($changePackageResponse->isSuccessful());
        $this->assertNull($changePackageResponse->getMessage());
    }

    public function testError()
    {
        $httpResponse = new Response(200, [], "
<changepackage>
    <result>
        <rawout>
        </rawout>
        <status>0</status>
        <statusmsg>The package name (testing) has not been defined in the reseller panel for the domain name example.com which these api keys are configured for. .   </statusmsg>
    </result>
</changepackage>
        ");

        $changePackageResponse = new ChangePackageResponse($httpResponse);

        $this->assertFalse($changePackageResponse->isSuccessful());
        $this->assertEquals(
            'The package name (testing) has not been defined in the reseller panel for the domain name example.com '.
            'which these api keys are configured for. .',
            $changePackageResponse->getMessage());
    }
}
