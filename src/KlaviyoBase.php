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
    // TODO: I don't think we can ever actually define a different host like this. Only calliing this throough __get in the Klaviyo class. Would like to change for local dev for example.
    public function __construct( $public_key, $private_key ) {
        $this->public_key = $public_key;
        $this->private_key = $private_key;
        $this->client = new Client(['base_uri' => self::HOST]);
    }

    /**
     * Make public API request
     */
    protected function publicRequest( $path, $options ) {
        // Public requests are always GET
        return $this->request( self::HTTP_GET, $path, $options, true );
    }

    /**
     * Make private v1 API request
     */
    protected function v1Request( $path, $options = [], $method = self::HTTP_GET ) {
        $path = self::V1 . $this->trimPath( $path );

        return $this->request( $method, $path, $options );
    }

    /**
     * Make private v2 API request
     */
    protected function v2Request( $path, $options = [], $method = self::HTTP_GET ) {
        $path = self::V2 . $this->trimPath( $path );

        return $this->request( $method, $path, $options );
    }

    /**
     * Make API request using HTTP client
     */
    private function request( $method, $path, $options, $isPublic = false ) {
        $this->prepareAuthentication( $options, $isPublic );

        $response = $this->client->request( $method, $path, $options );

        return $this->handleResponse( $response, $isPublic );
    }

    /**
     * Handle response from API call
     */
    private function handleResponse( ResponseInterface $response, $isPublic ) {
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
     * @param bool $isPublic Request type - public/private
     */
    private function prepareAuthentication ( &$options, $isPublic ) {
        if ( $isPublic ) {
            unset($options[self::HEADERS][self::API_KEY_HEADER]);

            $options = [
                self::QUERY => [
                    self::DATA => base64_encode(json_encode([self::TOKEN => $this->public_key] + $options[self::QUERY]))
                ]
            ];

            return;
        }

        $options = $options + [
            self::HEADERS => [self::API_KEY_HEADER => $this->private_key]
        ];
    }

    /**
     * Helper function to remove leading forward slashes
     */
    private function trimPath ( $path ) {
        return '/' . ltrim( $path, '/' );
    }

    /**
     * Return decoded json response as associative array. 
     */
    private function decodeJsonResponse ( ResponseInterface $response ) {
        return json_decode( $response->getBody(), true );
    }

    /**
     * Return formatted options.
     */
    protected function createOptions ( string $optionName, $optionValue ) {
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
    protected function isInstanceOf( $value, $key, $class ) {
        if (!($value instanceof $class)) {
            throw new KlaviyoException(
                sprintf('%s at key %s is not of type %s.', json_encode($value), $key, $class)
            );
        }
    }
}