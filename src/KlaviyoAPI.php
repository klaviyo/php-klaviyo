<?php

namespace Klaviyo;

use Klaviyo\Exception\KlaviyoApiException;
use Klaviyo\Exception\KlaviyoAuthenticationException;
use Klaviyo\Exception\KlaviyoException;
use Klaviyo\Exception\KlaviyoRateLimitException;
use Klaviyo\Exception\KlaviyoResourceNotFoundException;
use Klaviyo\Model\ProfileModel;

abstract class KlaviyoAPI
{
    /**
     * Host and versions
     */
    public const BASE_URL = 'https://a.klaviyo.com/api/';
    public const API_V1 = 'v1';
    public const API_V2 = 'v2';
    public const PACKAGE_VERSION = Klaviyo::VERSION;

    /**
     * Request methods
     */
    public const HTTP_GET = 'GET';
    public const HTTP_POST = 'POST';
    public const HTTP_PUT = 'PUT';
    public const HTTP_DELETE = 'DELETE';

    /**
     * Error messages
     */
    public const ERROR_INVALID_API_KEY = 'Invalid API Key.';
    public const ERROR_RESOURCE_DOES_NOT_EXIST = 'The requested resource does not exist.';

    /**
     * Request options
     */
    public const API_KEY_HEADER = 'api-key';
    public const API_KEY_PARAM = 'api_key';
    public const DATA = 'data';
    public const HEADERS = 'headers';
    public const JSON = 'json';
    public const PROPERTIES = 'properties';
    public const QUERY = 'query';
    public const TOKEN = 'token';
    public const USER_AGENT = 'User-Agent';

    /**
     * Shared endpoints
     */
    public const METRIC = 'metric';
    public const METRICS = 'metrics';
    public const RENDER = 'render';
    public const SEND = 'send';
    public const TIMELINE = 'timeline';

    /**
     * Klaviyo API arguments
     */
    public const PROFILES = 'profiles';
    public const COUNT = 'count';
    public const PAGE = 'page';
    public const SINCE = 'since';
    public const SORT = 'sort';

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
     */
    public function __construct($public_key, $private_key)
    {
        $this->public_key = $public_key;
        $this->private_key = $private_key;
    }

    /**
     * Make public API request
     *
     * @param Endpoint $path to call
     * @param API $options params to add to request
     */
    protected function publicRequest($path, $options)
    {
        // Public requests are always GET
        return $this->request(self::HTTP_GET, $path, $options, true);
    }

    /**
     * Make private v1 API request
     *
     * @param Endpoint $path to call
     * @param array $options API params to add to request
     * @param string $method HTTP method for request
     * @return mixed
     *
     * @throws KlaviyoException
     */
    protected function v1Request($path, $options = [], $method = self::HTTP_GET)
    {
        $path = self::API_V1 . $this->trimPath($path);

        return $this->request($method, $path, $options, false, true);
    }

    /**
     * Make private v2 API request
     *
     * @param Endpoint $path to call
     * @param array $options API params to add to request
     * @param string $method HTTP method for request
     * @return mixed
     *
     * @throws KlaviyoException
     */
    protected function v2Request($path, $options = [], $method = self::HTTP_GET)
    {
        $path = self::API_V2 . $this->trimPath($path);

        return $this->request($method, $path, $options, false, false);
    }

    /**
     * Make API request using HTTP client
     *
     * @param string $path Endpoint to call
     * @param array $options API params to add to request
     * @param string $method HTTP method for request
     * @param bool $isPublic to determine if public request
     * @param bool $isV1 to determine if V1 API request
     *
     * @throws KlaviyoException
     */
    private function request($method, $path, $options, $isPublic = false, $isV1 = false)
    {
        $options = $this->prepareAuthentication($options, $isPublic, $isV1);

        $setopt_array = (
            $this->getDefaultCurlOptions($method) +
            $this->getCurlOptUrl($path, $options) +
            $this->getSpecificCurlOptions($options)
        );

        $curl = curl_init();
        curl_setopt_array($curl, $setopt_array);

        $response = curl_exec($curl);
        $phpVersionHttpCode = version_compare(phpversion(), '5.5.0', '>') ? CURLINFO_RESPONSE_CODE : CURLINFO_HTTP_CODE;
        $statusCode = curl_getinfo($curl, $phpVersionHttpCode);
        curl_close($curl);

        return $this->handleResponse($response, $statusCode, $isPublic);
    }

    /**
     * Handle response from API call
     */
    private function handleResponse($response, $statusCode, $isPublic)
    {
        if ($statusCode == 403) {
            throw new KlaviyoAuthenticationException(self::ERROR_INVALID_API_KEY, $statusCode);
        } elseif ($statusCode == 404) {
            throw new KlaviyoResourceNotFoundException(self::ERROR_RESOURCE_DOES_NOT_EXIST, $statusCode);
        } elseif ($statusCode == 429) {
            throw new KlaviyoRateLimitException(
                $this->returnRateLimit($this->decodeJsonResponse($response), $statusCode)
            );
        } elseif ($statusCode != 200) {
            throw new KlaviyoApiException($this->decodeJsonResponse($response)['detail'], $statusCode);
        }

        if ($isPublic) {
            return $response;
        }

        return $this->decodeJsonResponse($response);
    }

    /**
     * Handle authentication by updating $options passed into request method
     * based on type of API request.
     *
     * @param array $params Options configuration for Request Interface
     * @param bool $isPublic Request type - public
     * @param bool $isV1 Request API version - V1
     *
     * @return array[]
     */
    private function prepareAuthentication($params, $isPublic, $isV1)
    {
        if ($isPublic) {
            $params = $this->publicAuth($params);
            return $params;
        }

        if ($isV1) {
            $params = $this->v1Auth($params);
            return $params;
        } else {
            $params = $this->v2Auth($params);
            return $params;
        }
    }

    /**
     * Setup authentication for Public Klaviyo API request
     *
     * @param $params
     * @return array[]
     */
    protected function publicAuth($params)
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
    protected function v1Auth($params)
    {
        $params = [
            self::QUERY => array_merge(
                $params,
                [ self::API_KEY_PARAM => $this->private_key ]
            )
        ];

        $params = $this->setUserAgentHeader($params);

        return $params;
    }

    /**
     * Setup authentication for Klaviyo API V2 request
     *
     * @param $params
     * @return array
     */
    protected function v2Auth($params)
    {
        $params = array_merge(
            $params,
            [
                self::HEADERS => [
                    self::API_KEY_HEADER => $this->private_key,
                    self::USER_AGENT => 'Klaviyo-PHP/' . self::PACKAGE_VERSION
                ]
            ]
        );

        return $params;
    }

    /**
     * Helper function to add UserAgent with package version to request
     *
     * @param $params
     * @return array
     */
    protected function setUserAgentHeader($params)
    {
        $params = array_merge(
            $params,
            [
                self::HEADERS => [
                    self::USER_AGENT => 'Klaviyo-PHP/' . self::PACKAGE_VERSION
                ]
            ]
        );

        return $params;
    }

    /**
     * Helper function to remove leading forward slashes
     */
    private function trimPath($path)
    {
        return '/' . ltrim($path, '/');
    }

    /**
     * Return decoded JSON response as associative or empty array.
     * Certain Klaviyo endpoints (such as Delete) return an empty string on success
     * and so PHP versions >= 7 will throw a JSON_ERROR_SYNTAX when trying to decode it
     *
     * @param string $response
     * @return mixed
     */
    private function decodeJsonResponse($response)
    {
        if (!empty($response)) {
            return json_decode($response, true);
        }
        return json_decode('{}', true);
    }

    /**
     * Return json encoded rate limit array with details and the retryAfter value parsed.
     * We build an easier object that tells you how long to retry after.
     *
     * @param mixed $response
     * @return string
     */
    private function returnRateLimit($response)
    {
        $responseDetail = explode(" ", $response['detail']);
        foreach ($responseDetail as $value) {
            if (intval($value) > 0) {
                $response['retryAfter'] = intval($value);
            }
        }
        return json_encode($response);
    }


    /**
     * Return formatted options.
     *
     * @param string $paramName Name of API Param to create
     * @param Value $paramValue of API params to create
     */
    protected function createParams($paramName, $paramValue)
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
    protected function isInstanceOf($value, $key, $class)
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
     * @param Timestamp $since as a state supplied for API request
     * @param New $uuid token supplied by API response
     * @return array
     */
    protected function setSinceParameter($since, $uuid)
    {
        if (is_null($uuid)) {
            return [
                self::SINCE => $since
            ];
        } else {
            return [
                self::SINCE => $uuid
            ];
        }
    }

    /**
     * Removes all params which do not have values set
     *
     * @param array $params
     * @return array
     */
    protected function filterParams($params)
    {
        return array_filter(
            $params,
            function ($key) {
                return !is_null($key);
            }
        );
    }

    /**
     * Structure params for V2 API requests
     *
     * @param array $params
     * @return array[]
     */
    protected function createRequestBody($params)
    {
        return [
            'form_params' => $params
        ];
    }

    /**
     * Structure params for V1 API requests
     *
     * @param array $params
     * @return array[]
     */
    protected function createRequestJson($params)
    {
        return [
            'json' => $params
        ];
    }

    /**
     * Check if a profile is an instance of ProfileModel
     *
     * @param array $profiles
     * @throws KlaviyoException
     */
    protected function checkProfile($profiles)
    {
        foreach ($profiles as $profile) {
            if (!$profile instanceof ProfileModel) {
                throw new KlaviyoException(
                    sprintf(
                    " %s is not an instance of ProfileModel, You must identify the person by their email, using a \$email key, or a unique identifier, using a \$id.",
                    $profile['$email']
                )
                );
            }
        }
    }

    /**
     * Get base options array for curl request.
     *
     * @param $method
     * @return array
     */
    protected function getDefaultCurlOptions($method)
    {
        return [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
        ];
    }

    /**
     * Build url for curl request.
     *
     * @param $path
     * @param $options
     * @return array
     */
    protected function getCurlOptUrl($path, $options)
    {
        $url = self::BASE_URL . $path;
        if (isset($options[self::QUERY])) {
            $url = $url . '?' . http_build_query($options[self::QUERY]);
        }

        return [CURLOPT_URL => $url];
    }

    /**
     * Build curl options array based on request data.
     *
     * @param $options
     * @return array
     */
    protected function getSpecificCurlOptions($options)
    {
        $setopt_array = [];
        if (isset($options[self::HEADERS])) {
            $setopt_array[CURLOPT_HTTPHEADER] = $this->formatCurlHeaders($options[self::HEADERS]);
        }
        if (isset($options[self::JSON])) {
            $setopt_array[CURLOPT_POSTFIELDS] = json_encode($options[self::JSON]);
            $setopt_array[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
        }
        if (isset($options['form_params'])) {
            $setopt_array[CURLOPT_POSTFIELDS] = http_build_query($options['form_params']);
        }

        return $setopt_array;
    }

    /**
     * Convert associative array of headers to HTTP header format.
     *
     * @param array $headers
     * @return array
     */
    protected function formatCurlHeaders($headers)
    {
        $formatted = [];

        foreach ($headers as $key => $value) {
            $formatted[] = $key . ': ' . $value;
        }

        return $formatted;
    }
}
