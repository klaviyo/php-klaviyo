<?php


use Klaviyo\KlaviyoAPI as Klaviyo;
use PHPUnit\Framework\TestCase;

class KlaviyoAPITest extends TestCase
{
    public $testKlaviyoClass;
    public $testAPI;

    public function setUp(): void
    {
        parent::setUp();

    }

    public function testPublicKey()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo{

            public function returnPublicKey( ){
                return $this->public_key;
            }
        };

        $this->assertEquals( 'Test0A', $this->testKlaviyoClass->returnPublicKey() );

    }

    public function testPrivateKey()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo{

            public function returnPrivateKey( ){
                return $this->private_key;
            }
        };

        $this->assertEquals( 'pk_testprivatekey', $this->testKlaviyoClass->returnPrivateKey() );

    }

    public function testClient()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo{

            public function returnClient(){
                return $this->client;
            }
        };

        $this->assertInstanceOf(\GuzzleHttp\Client::class, $this->testKlaviyoClass->returnClient() );

    }

    public function testPublicAuth()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo{

            public function returnPublicAuth( $params ){
                return $this->publicAuth( $params );
            }
        };

        $methodInput = array(
            'headers' => array(
                'api-key' => ''
            ),
            'query' => array()
        );

        $expected = array(
            'query' => array(
                'data' => 'eyJ0b2tlbiI6IlRlc3QwQSJ9'
            )
        );

        $this->assertEquals( $expected, $this->testKlaviyoClass->returnPublicAuth( $methodInput ));
    }

    public function testV1Auth()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo {

            public function returnV1Auth( $params )
            {
                return $this->v1Auth( $params );
            }
        };

        $expected = array(
            'query' => array(
                'api_key' => 'pk_testprivatekey'
            ),
            'headers' => array(
                'user-agent' => 'Klaviyo-PHP/2.0.0'
            )
        );

        $this->assertEquals( $expected, $this->testKlaviyoClass->returnV1Auth( [] ) );
    }

    public function testV2Auth()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo {

            public function returnV2Auth( $params )
            {
                return $this->v2Auth( $params );
            }
        };

        $expected = array(
            'headers' => array(
                'api-key' => 'pk_testprivatekey',
                'user-agent' => 'Klaviyo-PHP/2.0.0'
            )
        );

        $this->assertEquals( $expected, $this->testKlaviyoClass->returnV2Auth( [] ) );

    }

    public function testSetUserAgentHeader()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo{

            public function returnUserAgentHeader( $query ){
                return $this->setUserAgentHeader( $query );
            }
        };

        $testHeader = array(
            'headers' => array(
                'user-agent' => 'Klaviyo-PHP/2.0.0'
            )
        );

        $this->assertEquals( $testHeader, $this->testKlaviyoClass->returnUserAgentHeader( [] ));

    }

    public function testCreateParams()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo {

            public function returnCreateParams( $name, $value )
            {
                return $this->createParams( $name, $value );
            }
        };

        $expected = array(
            'json' => array(
                'testName' => 'testValue'
            )
        );

        $this->assertEquals( $expected, $this->testKlaviyoClass->returnCreateParams( 'testName', 'testValue' ) );

    }

    public function testSetSinceParameter()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo {

            public function returnSetSinceParameter( $value1, $value2 )
            {
                return $this->setSinceParameter( $value1, $value2 );
            }
        };

        $expected1 = array(
            'since' => 'since'
        );

        $expected2 = array(
            'since' => 'uuid'
        );

        $this->assertEquals( $expected1, $this->testKlaviyoClass->returnSetSinceParameter( 'since', NULL ) );
        $this->assertEquals( $expected2, $this->testKlaviyoClass->returnSetSinceParameter( 'since', 'uuid'));
    }

    public function testFilterParams()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo {

            public function returnFilterParams( $params )
            {
                return $this->filterParams( $params );
            }
        };

        $input = array(
            'key1' => 'value1',
            'key2' => NULL,
            'key3' => 'value2'
        );

        $expected = array(
            'key1' => 'value1',
            'key3' => 'value2'
        );

        $this->assertEquals( $expected, $this->testKlaviyoClass->returnFilterParams( $input ) );
    }
    public function testCreateRequestBody()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo {

            public function returnCreateRequestBody( $params )
            {
                return $this->createRequestBody( $params );
            }
        };

        $expected = array(
            'form_params' => []
        );

        $this->assertEquals( $expected, $this->testKlaviyoClass->returnCreateRequestBody( [] ) );
    }

    public function testCreateRequestJson()
    {
        $this->testKlaviyoClass = new class('Test0A', 'pk_testprivatekey') extends Klaviyo {

            public function returnCreateRequestJson( $params )
            {
                return $this->createRequestJson( $params );
            }
        };

        $expected = array(
            'json' => []
        );

        $this->assertEquals( $expected, $this->testKlaviyoClass->returnCreateRequestJson( [] ) );
    }

}
