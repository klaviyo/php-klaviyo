<?php

namespace Klaviyo;

use Klaviyo\Exception\KlaviyoException;

use GuzzleHttp\Client;
use Klaviyo\Model\ProfileModel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

abstract class KlaviyoAPI
{
    /**
     * Host and versions
     */
    const BASE_URL = 'https://a.klaviyo.com/api/';
    const API_V1 = 'v1';
    const API_V2 = 'v2';
    const PACKAGE_VERSION = Klaviyo::VERSION;

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
    const USER_AGENT = 'user-agent';

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
    const PROFILES = 'profiles';
    const COUNT = 'count';
    const PAGE = 'page';
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
     * @var string
     */
    protected $client;

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
        $this->client = new Client(['base_uri' => self::BASE_URL]);
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->private_key;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->public_key;
    }

    /**
     * Make public API request
     *
     * @param $path Endpoint to call
     * @param $options API params to add to request
     */
    protected function publicRequest( $path, $options )
    {
        // Public requests are always GET
        return $this->request( self::HTTP_GET, $path, $options, true );
    }

    /**
     * Make private v1 API request
     *
     * @param $path Endpoint to call
     * @param array $options API params to add to request
     * @param string $method HTTP method for request
     * @return mixed|StreamInterface
     *
     * @throws KlaviyoException
     */
    protected function v1Request( $path, $options = [], $method = self::HTTP_GET )
    {
        $path = self::API_V1 . $this->trimPath( $path );

        return $this->request( $method, $path, $options, false, true );
    }

    /**
     * Make private v2 API request
     *
     * @param $path Endpoint to call
     * @param array $options API params to add to request
     * @param string $method HTTP method for request
     * @return mixed|StreamInterface
     *
     * @throws KlaviyoException
     */
    protected function v2Request( $path, $options = [], $method = self::HTTP_GET )
    {
        $path = self::API_V2 . $this->trimPath( $path );

        return $this->request( $method, $path, $options, false, false );
    }

    /**
     * Make API request using HTTP client
     *
     * @param $path Endpoint to call
     * @param $options API params to add to request
     * @param string $method HTTP method for request
     * @param bool $isPublic to determine if public request
     * @param bool $isV1 to determine if V1 API request
     *
     * @throws KlaviyoException
     */
    private function request( $method, $path, $options, $isPublic = false, $isV1 = false )
    {
        $options = $this->prepareAuthentication( $options, $isPublic, $isV1 );

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
            return $response->getBody();
        }

        return $this->decodeJsonResponse( $response );
    }

    /**
     * Handle authentication by updating $options passed into request method
     * based on type of API request.
     *
     * @param array $params Options configuration for Request Interface
     * @param bool $isPublic Request type - public
     * @param bool $isV1 Request API version - V1
     * @param bool $isV2 Request API version - V2
     */
    private function prepareAuthentication ( $params, $isPublic, $isV1 )
    {
        if ( $isPublic ) {
            $params = $this->publicAuth( $params );
            return $params;
        }

        if ( $isV1 ) {
            $params = $this->v1Auth( $params );
            return $params;
        } else {
            $params = $this->v2Auth( $params );
            return $params;
        }

    }

    /**
     * Setup authentication for Public Klaviyo API request
     *
     * @param $params
     * @return array[]
     */
    protected function publicAuth( $params )
    {
        unset($params[self::HEADERS][self::API_KEY_HEADER]);

        $params = [
            self::QUERY => [
                self::DATA => base64_encode(
                    json_encode(
                        array_merge(
                            [self::TOKEN => $this->public_key],
                            $params[self::QUERY]
                        )
                    )
                )
            ]
        ];

        return $params;
    }

    /**
     * Setup authentication for Klaviyo API V1 request
     *
     * @param $params
     * @return array
     */
    protected function v1Auth( $params )
    {
        $params = array(
            self::QUERY => array_merge(
                $params,
                array( self::API_KEY_PARAM => $this->private_key )
            )
        );

        $params = $this->setUserAgentHeader( $params );

        return $params;
    }

    /**
     * Setup authentication for Klaviyo API V2 request
     *
     * @param $params
     * @return array
     */
    protected function v2Auth( $params )
    {
        $params = array_merge(
            $params,
            array(
                self::HEADERS => array(
                    self::API_KEY_HEADER => $this->private_key,
                    self::USER_AGENT => 'Klaviyo-PHP/' . self::PACKAGE_VERSION
                )
            )
        );

        return $params;
    }

    /**
     * Helper function to add UserAgent with package version to request
     *
     * @param $params
     * @return array
     */
    protected function setUserAgentHeader( $params )
    {
        $params = array_merge(
            $params,
            array(
                self::HEADERS => array(
                    self::USER_AGENT => 'Klaviyo-PHP/' . self::PACKAGE_VERSION
                )
            )
        );

        return $params;
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
     *
     * @param ResponseInterface $response
     * @return mixed
     */
    private function decodeJsonResponse( ResponseInterface $response )
    {
        return json_decode( $response->getBody(), true );
    }

    /**
     * Return formatted options.
     *
     * @param string $paramName Name of API Param to create
     * @param $paramValue Value of API params to create
     */
    protected function createParams( string $paramName, $paramValue )
    {
        return [self::JSON =>
            [$paramName => $paramValue]
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

    /**
     * Determine what value to set for the since request param
     *
     * @param $since Timestamp as a state supplied for API request
     * @param $uuid New token supplied by API response
     * @return array
     */
    protected function setSinceParameter( $since, $uuid )
    {
        if ( is_null( $uuid )) {
            return array(
                self::SINCE => $since
            );
        } else {
            return array(
                self::SINCE => $uuid
            );
        }
    }

    /**
     * Removes all params which do not have values set
     *
     * @param array $params
     * @return array
     */
    protected function filterParams( array $params )
    {
        return array_filter(
            $params,
            function ( $key ){ return !is_null( $key ); },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Structure params for V2 API requests
     *
     * @param array $params
     * @return array[]
     */
    protected function createRequestBody( array $params )
    {
        return array(
            'form_params' => $params
        );
    }

    /**
     * Structure params for V1 API requests
     *
     * @param array $params
     * @return array[]
     */
    protected function createRequestJson( array $params)
    {
        return array(
            'json' => $params
        );
    }

    /**
     * Check if a profile is an instance of ProfileModel
     *
     * @param array $profiles
     * @throws KlaviyoException
     */
    protected function checkProfile( array $profiles )
    {
        foreach ( $profiles as $profile ) {
            if ( ! $profile instanceof ProfileModel ) {
                throw new KlaviyoException( sprintf( " %s is not an instance of %s, You must identify the person by their email, using a \$email key, or a unique identifier, using a \$id.",
                    $profile['$email'],
                    ProfileModel::class )
                );
            }
        }
    }

}
