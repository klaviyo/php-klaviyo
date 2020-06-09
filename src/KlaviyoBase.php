<?php

namespace Klaviyo;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Klaviyo\Exception\KlaviyoException;
use Klaviyo\Model\ProfileModel;

abstract class KlaviyoBase
{
    /**
     * Host and versions
     */
    const HOST = 'https://a.klaviyo.com/api/';
    const V1 = 'v1';
    const V2 = 'v2';

    /**
     * Request methods
     */
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';

    /**
     * Error messages
     */
    const ERROR_INVALID_API_KEY = 'Invalid API Key.';
    const ERROR_RESOURCE_DOES_NOT_EXIST = 'The requested resource does not exist.';
    const ERROR_NON_200_STATUS = 'Request Failed with HTTP Status Code: %s';
    
    /**
     * Request options
     */
    const API_KEY_HEADER = 'api-key';
    const API_KEY_PARAM = 'api_key';
    const DATA = 'data';
    const HEADERS = 'headers';
    const JSON = 'json';
    const PROPERTIES = 'properties';
    const QUERY = 'query';
    const TOKEN = 'token';

    /**
     * Shared endpoints
     */
    const METRIC = 'metric';
    const METRICS = 'metrics';
    const RENDER = 'render';
    const SEND = 'send';
    const TIMELINE = 'timeline';

    /**
     * Klaviyo API arguments
     */
    const COUNT = 'count';
    const EMAILS = 'emails';
    const PAGE = 'page';
    const PROFILES = 'profiles';
    const SINCE = 'since';
    const SORT = 'sort';

    /**
     * @var string
     */
    protected $private_key;

    /**
     * @var string
     */
    protected $public_key;

    /**
     * Constructor method for Base class.
     *
     * @param string $public_key Public key (account ID) for Klaviyo account
     * @param string $private_key Private API key for Klaviyo account
     * @param string $host Base URI for API requests. Can be overridden for testing.
     */
    // TODO: I don't think we can ever actually define a different host like this. Only calling this through __get in the Klaviyo class. Would like to change for local dev for example.
    public function __construct( $public_key, $private_key )
    {
        $this->public_key = $public_key;
        $this->private_key = $private_key;
        $this->client = new Client(['base_uri' => self::HOST]);
    }

    public function getPublicKkey()
    {
        return $this->public_key;
    }

    public function getPrivateKey()
    {
        return $this->private_key;
    }

    /**
     * Make public API request
     */
    protected function publicRequest( $path, $options )
    {
        // Public requests are always GET
        return $this->request( self::HTTP_GET, $path, $options, true );
    }

    /**
     * Make private v1 API request
     */
    protected function v1Request( $path, $options = [], $method = self::HTTP_GET )
    {
        $path = self::V1 . $this->trimPath( $path );

        return $this->request( $method, $path, $options, false, true );
    }

    /**
     * Make private v2 API request
     */
    protected function v2Request( $path, $options = [], $method = self::HTTP_GET )
    {
        $path = self::V2 . $this->trimPath( $path );

        return $this->request( $method, $path, $options, false, false, true );
    }

    /**
     * Make API request using HTTP client
     */
    private function request( $method, $path, $options, $isPublic = false, $isV1 = false, $isV2 = false )
    {
        $this->prepareAuthentication( $options, $isPublic, $isV1, $isV2 );

        $response = $this->client->request( $method, $path, $options );

        return $this->handleResponse( $response, $isPublic );
    }

    /**
     * Handle response from API call
     */
    private function handleResponse( ResponseInterface $response, $isPublic )
    {
        $statusCode = $response->getStatusCode();

        if ( $statusCode == 403 ) {
            throw new KlaviyoAuthenticationException(self::ERROR_INVALID_API_KEY);
        } else if ( $statusCode == 404 ) {
            throw new KlaviyoResourceNotFoundException(self::ERROR_RESOURCE_DOES_NOT_EXIST);
        } else if ( $statusCode == 429 ) {
            throw new KlaviyoRateLimitException( $this->decodeJsonResponse( $response ) );
        } else if ( $statusCode != 200 ) {
            throw new KlaviyoException( sprintf( self::ERROR_NON_200_STATUS, $statusCode ) );
        }

        if ( $isPublic ) {
            return '1' == $response->getBody();
        }

        return $this->decodeJsonResponse( $response );
    }

    /**
     * Handle authentication by updating $options passed into request method
     * based on type of API request.
     *
     * @param array $options Options configuration for Request Interface
     * @param bool $isPublic Request type - public
     * @param bool $isV1 Request API version - V1
     * @param bool $isV2 Request API version - V2
     */
    private function prepareAuthentication ( &$options, $isPublic, $isV1, $isV2 )
    {
        if ( $isPublic ) {
            unset($options[self::HEADERS][self::API_KEY_HEADER]);

            $options = [
                self::QUERY => [
                    self::DATA => base64_encode(json_encode([self::TOKEN => $this->public_key] + $options[self::QUERY]))
                ]
            ];

            return;
        }

        if ( $isV1 ) {
            $options = array(
                self::QUERY => array_merge(
                    $options,
                    array( self::API_KEY_PARAM => $this->private_key )
                )
            );

            return;
        }

        if ( $isV2 ) {
            $options = array_merge(
                $options,
                array(
                    self::HEADERS => array(
                        self::API_KEY_HEADER => $this->private_key
                    )
                )
            );

            return;
        }
    }

    /**
     * Helper function to remove leading forward slashes
     */
    private function trimPath ( $path )
    {
        return '/' . ltrim( $path, '/' );
    }

    /**
     * Return decoded json response as associative array. 
     */
    private function decodeJsonResponse ( ResponseInterface $response )
    {
        return json_decode( $response->getBody(), true );
    }

    /**
     * Return formatted options.
     */
    protected function  createOptions ( string $optionName, $optionValue )
    {
        return [self::JSON =>
            [$optionName => $optionValue]
        ];
    }

    /**
     * Check if item is of a specific type. To be used with array_walk for
     * checking all items of an array are of a certain type.
     *
     * @param mixed $value Value of item in array.
     * @param mixed $key Key of item in array.
     * @param string $class Name of class against which items are validated.
     */
    protected function isInstanceOf( $value, $key, $class )
    {
        if (!($value instanceof $class)) {
            throw new KlaviyoException(
                sprintf('%s at key %s is not of type %s.', json_encode($value), $key, $class)
            );
        }
    }

    protected function setSinceParameter( $since, $uuid )
    {
        if ( is_null( $uuid )) {
            return array(
                'since' => $since
            );
        } else {
            return array(
                'since' => $uuid
            );
        }
    }

    protected function filterParams( array $params )
    {
        return array_filter(
            $params,
            function ( $key ){ return !is_null( $key ); },
            ARRAY_FILTER_USE_BOTH
        );
    }

    protected function createRequestBody( array $params )
    {
        return array(
            'form_params' => $params
        );
    }

    protected function createRequestJson( array $params)
    {
        return array(
            'json' => $params
        );
    }

}