<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\ListPackagesResponse;
use PHPUnit\Framework\TestCase;

class ListPackagesResponseTest extends TestCase
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
        $packages = [
            [
                'name' => 'Default',
                'BWLIMIT' => '1000',
                'QUOTA' => '100',
            ],
            [
                'name' => 'Premium',
                'BWLIMIT' => '10000',
                'QUOTA' => '1000',
            ]
        ];

        $httpResponse = new Response(200, [], json_encode(['packages' => $packages]));

        $listPackagesResponse = new ListPackagesResponse($httpResponse);

        $this->assertTrue($listPackagesResponse->isSuccessful());
        $this->assertEquals($packages, $listPackagesResponse->getPackages());
        $this->assertNull($listPackagesResponse->getMessage());
    }

    public function testError()
    {
        $message = 'The API username you are using appears to be invalid 1. 259';

        $httpResponse = new Response(200, [], json_encode([
            'cpanelresult' => [
                'apiversion' => '2',
                'error' => $message,
                'data' => [
                    'reason' => 'The API username you are using appears to be invalid 1. 259',
                    'result' => '0',
                ],
                'type' => 'text',
            ],
        ]));

        $listPackagesResponse = new ListPackagesResponse($httpResponse);

        $this->assertFalse($listPackagesResponse->isSuccessful());
        $this->assertNull($listPackagesResponse->getPackages());
        $this->assertEquals($message, $listPackagesResponse->getMessage());
    }
}
