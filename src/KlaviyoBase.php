<?php

namespace Klaviyo;

use GuzzleHttp\Client;

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
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    /**
     * Request options
     */
    const API_KEY_HEADER = 'api-key';
    const DATA = 'data';
    const HEADERS = 'headers';
    const JSON = 'json';
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
     * Constructor method for Base class.
     *
     * @param string $public_key Public key (account ID) for Klaviyo account
     * @param string $private_key Private API key for Klaviyo account
     * @param string $host Base URI for API requests. Can be overridden for testing.
     */
    // TODO: I don't think we can ever actually define a different host like this. Only calliing this throough __get in the Klaviyo class
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
        return $this->request( self::GET, $path, $options, true );
    }

    /**
     * Make private v1 API request
     */
    protected function v1Request( $path, $options = [], $method = self::GET ) {
        $path = self::V1 . $this->trimPath( $path );

        return $this->request( $method, $path, $options );
    }

    /**
     * Make private v2 API request
     */
    protected function v2Request( $path, $options = [], $method = self::GET ) {
        $path = self::V2 . $this->trimPath( $path );

        return $this->request( $method, $path, $options );
    }

    /**
     * Make API request using HTTP client
     */
    private function request( $method, $path, $options, $isPublic = false ) {
        $this->prepareAuthentication( $options, $isPublic );

        $response = $this->client->request( $method, $path, $options);

        return json_decode($response->getBody(), true);
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
}