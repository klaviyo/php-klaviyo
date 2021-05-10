<?php

declare(strict_types=1);

namespace Klaviyo;

use Klaviyo\Exception\KlaviyoAuthenticationException;
use Klaviyo\Exception\KlaviyoException;
use Klaviyo\Exception\KlaviyoRateLimitException;
use Klaviyo\Exception\KlaviyoResourceNotFoundException;
use Klaviyo\Exception\KlaviyoApiException;
use Klaviyo\Model\ProfileModel;
use JsonException;

class KlaviyoAPI
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
    const USER_AGENT = 'User-Agent';

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
    const EMAIL = 'email';
    const PAGE = 'page';
    const PROFILES = 'profiles';
    const SINCE = 'since';
    const SORT = 'sort';

    protected string $private_key;

    protected string $public_key;

    /**
     * Constructor method for Base class.
     *
     * @param string $public_key Public key (account ID) for Klaviyo account
     * @param string $private_key Private API key for Klaviyo account
     */
    public function __construct(string $public_key, string $private_key)
    {
        $this->public_key = $public_key;
        $this->private_key = $private_key;
    }

    /**
     * Make public API request
     *
     * @param string $path Endpoint to call
     * @param array $options API params to add to request
     *
     * @return array
     * @throws KlaviyoException
     */
    public function publicRequest(string $path, array $options) : array
    {
        // Public requests are always GET
        return $this->request(self::HTTP_GET, $path, $options, true);
    }

    /**
     * Make private v1 API request
     *
     * @param string $path Endpoint to call
     * @param array $options API params to add to request
     * @param string $method HTTP method for request
     *
     * @return array
     * @throws KlaviyoException
     */
    public function v1Request(string $path, array $options = [], string $method = self::HTTP_GET) : array
    {
        $path = self::API_V1 . $this->trimPath($path);

        return $this->request($method, $path, $options, false, true);
    }

    /**
     * Make private v2 API request
     *
     * @param string $path Endpoint to call
     * @param array $options API params to add to request
     * @param string $method HTTP method for request
     *
     * @return array
     * @throws KlaviyoException
     */
    public function v2Request(string $path, array $options = [], string $method = self::HTTP_GET) : array
    {
        $path = self::API_V2 . $this->trimPath($path);

        return $this->request($method, $path, $options);
    }

    /**
     * Return formatted options.
     *
     * @param string $paramName Name of API Param to create
     * @param string|array $paramValue Value of API params to create
     *
     * @return array
     */
    public function createParams(string $paramName, $paramValue) : array
    {
        return [
            self::JSON => [$paramName => $paramValue],
        ];
    }

    /**
     * Determine what value to set for the since request param
     *
     * @param string|null $since Timestamp as a state supplied for API request
     * @param string|null $uuid New token supplied by API response
     *
     * @return array
     */
    public function setSinceParameter(?string $since, ?string $uuid = null) : array
    {
        if (is_null($uuid)) {
            return [
                self::SINCE => $since,
            ];
        }
        return [
            self::SINCE => $uuid,
        ];
    }

    /**
     * Removes all params which do not have values set
     *
     * @param array $params
     *
     * @return array
     */
    public function filterParams(array $params) : array
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
     *
     * @return array[]
     */
    public function createRequestBody(array $params) : array
    {
        return [
            'form_params' => $params,
        ];
    }

    /**
     * Structure params for V1 API requests
     *
     * @param array $params
     *
     * @return array[]
     */
    public function createRequestJson(array $params) : array
    {
        return [
            'json' => $params,
        ];
    }

    /**
     * Check if a profile is an instance of ProfileModel
     *
     * @param array $profiles
     *
     * @throws KlaviyoException
     */
    public function checkProfile(array $profiles) : void
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
     * Setup authentication for Public Klaviyo API request
     *
     * @param array $params
     *
     * @return array[]
     */
    private function publicAuth($params) : array
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
                ),
            ],
        ];

        return $params;
    }

    /**
     * Setup authentication for Klaviyo API V1 request
     *
     * @param array $params
     * @return array
     */
    private function v1Auth(array $params) : array
    {
        $params = [
            self::QUERY => array_merge(
                $params,
                [self::API_KEY_PARAM => $this->private_key]
            ),
        ];

        $params = $this->setUserAgentHeader($params);

        return $params;
    }

    /**
     * Setup authentication for Klaviyo API V2 request
     *
     * @param array $params
     * @return array
     */
    private function v2Auth(array $params) : array
    {
        $params = array_merge(
            $params,
            [
                self::HEADERS => [
                    self::API_KEY_HEADER => $this->private_key,
                    self::USER_AGENT => 'Klaviyo-PHP/' . self::PACKAGE_VERSION,
                ],
            ]
        );

        return $params;
    }

    /**
     * Helper function to add UserAgent with package version to request
     *
     * @param array $params
     * @return array
     */
    private function setUserAgentHeader(array $params) : array
    {
        $params = array_merge(
            $params,
            [
                self::HEADERS => [
                    self::USER_AGENT => 'Klaviyo-PHP/' . self::PACKAGE_VERSION,
                ],
            ]
        );

        return $params;
    }

    /**
     * Get base options array for curl request.
     *
     * @param string $method
     * @return array
     */
    private function getDefaultCurlOptions(string $method) : array
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
     * @param string $path
     * @param array $options
     * @return array
     */
    private function getCurlOptUrl(string $path, array $options) : array
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
     * @param array $options
     * @return array
     */
    private function getSpecificCurlOptions(array $options) : array
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
    private function formatCurlHeaders(array $headers) : array
    {
        $formatted = [];

        foreach ($headers as $key => $value) {
            $formatted[] = $key . ': ' . $value;
        }

        return $formatted;
    }

    /**
     * Make API request using HTTP client
     *
     * @param string $path Endpoint to call
     * @param array $options API params to add to request
     * @param string $method HTTP method for request
     * @param bool $isPublic to determine if public request
     * @param bool $isV1 to determine if V1 API request
     * @return array
     *
     * @throws JsonException
     * @throws KlaviyoAuthenticationException
     * @throws KlaviyoException
     * @throws KlaviyoRateLimitException
     * @throws KlaviyoResourceNotFoundException
     */
    private function request(
        string $method,
        string $path,
        array $options,
        bool $isPublic = false,
        bool $isV1 = false
    ) : array {
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

        if (!$response) {
            throw new KlaviyoException('Curl response failed');
        }

        return $this->handleResponse($response, $statusCode, $isPublic);
    }

    /**
     * Handle response from API call
     *
     * @param string $response
     * @param int $statusCode
     * @param bool $isPublic
     * @return array|string
     * @throws KlaviyoAuthenticationException
     * @throws KlaviyoException
     * @throws KlaviyoRateLimitException
     * @throws KlaviyoResourceNotFoundException
     * @throws JsonException
     */
    private function handleResponse(string $response, int $statusCode, bool $isPublic)
    {
        if ($statusCode == 403) {
            throw new KlaviyoAuthenticationException(self::ERROR_INVALID_API_KEY, $statusCode);
        } elseif ($statusCode == 429) {
            throw new KlaviyoRateLimitException(
                $this->returnRateLimit($this->decodeJsonResponse($response))
            );
        } elseif ($statusCode < 200 || $statusCode >= 300) {
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
     * @return array|array[]
     */
    private function prepareAuthentication(array $params, bool $isPublic, bool $isV1) : array
    {
        if ($isPublic) {
            $params = $this->publicAuth($params);
            return $params;
        }

        if ($isV1) {
            $params = $this->v1Auth($params);
            return $params;
        }
        $params = $this->v2Auth($params);
        return $params;
    }

    /**
     * Helper function to remove leading forward slashes
     *
     * @param string $path
     * @return string
     */
    private function trimPath(string $path) : string
    {
        return '/' . ltrim($path, '/');
    }

    /**
     * Return decoded JSON response as associative or empty array.
     * Certain Klaviyo endpoints (such as Delete) return an empty string on success
     * and so PHP versions >= 7 will throw a JSON_ERROR_SYNTAX when trying to decode it
     *
     * @param string $response
     * @return array
     */
    private function decodeJsonResponse(string $response) : array
    {
        if (!empty($response)) {
            return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        }
        return json_decode('{}', true);
    }

    /**
     * Return json encoded rate limit array with details and the retryAfter value parsed.
     * We build an easier object that tells you how long to retry after.
     *
     * @param array $response
     * @return string
     */
    private function returnRateLimit(array $response) : string
    {
        $responseDetail = explode(" ", $response['detail']);
        foreach ($responseDetail as $value) {
            if (intval($value) > 0) {
                $response['retryAfter'] = intval($value);
            }
        }
        return json_encode($response);
    }
}
