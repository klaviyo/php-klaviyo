<?php

namespace Klaviyo;

use Klaviyo\Exception\KlaviyoAuthenticationException;
use Klaviyo\Exception\KlaviyoException;
use Klaviyo\Exception\KlaviyoRateLimitException;
use Klaviyo\Exception\KlaviyoResourceNotFoundException;
use Klaviyo\Exception\KlaviyoApiException;
use Klaviyo\Model\EventModel;
use Klaviyo\Model\ProfileModel;


abstract class KlaviyoAPI
{
    /**
     * Track Class constants
     */
    const TRACK = 'track';
    const IDENTIFY = 'identify';

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
     * @param $path Endpoint to call
     * @param $options API params to add to request
     */
    #[\ReturnTypeWillChange]
    protected function publicRequest($path, $options, $post)
    {
        $method = $post ? self::HTTP_POST : self::HTTP_GET;
        return $this->request($method, $path, $options, true);
    }

    /**
     * Make private v1 API request
     *
     * @param string $path Endpoint to call
     * @param array $options API params to add to request
     * @param string $method HTTP method for request
     * @return mixed
     *
     * @throws KlaviyoException
     */
    #[\ReturnTypeWillChange]
    protected function v1Request($path, $options = [], $method = self::HTTP_GET)
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
     * @return mixed
     *
     * @throws KlaviyoException
     */
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    private function request($method, $path, $options, $isPublic = false, $isV1 = false)
    {
        $options = $this->prepareAuthentication($method, $options, $isPublic, $isV1);

        $setopt_array = ($this->getDefaultCurlOptions($method) +
            $this->getCurlOptUrl($path, $options) +
            $this->getSpecificCurlOptions($options));

        $curl = curl_init();
        curl_setopt_array($curl, $setopt_array);

        $response = curl_exec($curl);
        $phpVersionHttpCode =  version_compare(phpversion(), '5.5.0', '>') ? CURLINFO_RESPONSE_CODE : CURLINFO_HTTP_CODE;
        $statusCode = curl_getinfo($curl, $phpVersionHttpCode);
        curl_close($curl);

        return $this->handleResponse($response, $statusCode, $isPublic);
    }

    /**
     * Handle response from API call
     */
    #[\ReturnTypeWillChange]
    private function handleResponse($response, $statusCode, $isPublic)
    {
        $decoded_response = $this->decodeJsonResponse($response);
        if ($statusCode == 403) {
            throw new KlaviyoAuthenticationException(self::ERROR_INVALID_API_KEY, $statusCode);
        } else if ($statusCode == 429) {
            throw new KlaviyoRateLimitException(
                $this->returnRateLimit($decoded_response)
            );
        } else if ($statusCode < 200 || $statusCode >= 300) {
            throw new KlaviyoApiException(isset($decoded_response['detail']) ? $decoded_response['detail'] : sprintf(self::ERROR_NON_200_STATUS, $statusCode), $statusCode);
        }

        if ($isPublic) {
            return $response;
        }

        return $decoded_response;
    }

    /**
     * Handle authentication by updating $options passed into request method
     * based on type of API request.
     *
     * @param string $method Request method type - POST or GET
     * @param array $params Options configuration for Request Interface
     * @param bool $isPublic Request type - public
     * @param bool $isV1 Request API version - V1
     *
     * @return array|array[]
     */
    #[\ReturnTypeWillChange]
    private function prepareAuthentication($method, $params, $isPublic, $isV1)
    {
        if ($isPublic && $method == self::HTTP_POST) {
            $params = $this->publicPostAuth($params);
            return $params;
        }

        if ($isPublic) {
            $params = $this->publicGetAuth($params);
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
     * Setup authentication for Public POST Klaviyo API request
     *
     * @param $params
     * @return array[]
     */
    #[\ReturnTypeWillChange]
    protected function publicPostAuth($params)
    {
        unset($params[self::HEADERS][self::API_KEY_HEADER]);
        $params = array_merge_recursive(
            $params,
            [self::JSON => [self::TOKEN => $this->public_key]]
        );
        return $params;
    }

    /**
     * Setup authentication for Public GET Klaviyo API request
     *
     * @param $params
     * @return array[]
     */
    #[\ReturnTypeWillChange]
    protected function publicGetAuth($params)
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
    #[\ReturnTypeWillChange]
    protected function v1Auth($params)
    {
        $params[self::QUERY][self::API_KEY_PARAM] = $this->private_key;

        $params = $this->setUserAgentHeader($params);

        return $params;
    }

    /**
     * Setup authentication for Klaviyo API V2 request
     *
     * @param $params
     * @return array
     */
    #[\ReturnTypeWillChange]
    protected function v2Auth($params)
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
    #[\ReturnTypeWillChange]
    protected function setUserAgentHeader($params)
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
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
     * @param mixed $paramValue Value of API params to create
     */
    #[\ReturnTypeWillChange]
    protected function createParams($paramName, $paramValue)
    {
        return [
            self::JSON =>
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
    #[\ReturnTypeWillChange]
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
     * @param $since Timestamp as a state supplied for API request
     * @param $uuid New token supplied by API response
     * @return array
     */
    #[\ReturnTypeWillChange]
    protected function setSinceParameter($since, $uuid)
    {
        if (is_null($uuid)) {
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
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    protected function createRequestBody($params)
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
    #[\ReturnTypeWillChange]
    protected function createRequestJson($params)
    {
        return array(
            'json' => $params
        );
    }
    /**
     * Create options array for either Track or Identify call.
     *
     * @param string $type Request type - track or identify
     * @param ProfileModel|EventModel $model
     * @return array
     */
    #[\ReturnTypeWillChange]
    protected function createOptionsArray($type, $model)
    {
        if ($type == self::TRACK) {
            return array(self::QUERY => $model->toArray());
        }

        return array(
            self::QUERY => array(
                self::PROPERTIES => $model
            )
        );
    }

    /**
     * Create query parameter array for CURL.
     *
     * @param array $params Key/value pairs to be set as query string.
     * @return array
     */
    #[\ReturnTypeWillChange]
    protected function createQueryParams($params)
    {
        return array(
            self::QUERY => $params
        );
    }

    /**
     * Check if a profile is an instance of ProfileModel
     *
     * @param array $profiles
     * @throws KlaviyoException
     */
    #[\ReturnTypeWillChange]
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
    #[\ReturnTypeWillChange]
    protected function getDefaultCurlOptions($method)
    {
        return array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
        );
    }

    /**
     * Build url for curl request.
     *
     * @param $path
     * @param $options
     * @return array
     */
    #[\ReturnTypeWillChange]
    protected function getCurlOptUrl($path, $options)
    {
        $url = self::BASE_URL . $path;
        if (isset($options[self::QUERY])) {
            $url = $url . '?' . http_build_query($options[self::QUERY]);
        }

        return array(CURLOPT_URL => $url);
    }

    /**
     * Build curl options array based on request data.
     *
     * @param $options
     * @return array
     */
    #[\ReturnTypeWillChange]
    protected function getSpecificCurlOptions($options)
    {
        $setopt_array = array();
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
    #[\ReturnTypeWillChange]
    protected function formatCurlHeaders($headers)
    {
        $formatted = array();

        foreach ($headers as $key => $value) {
            $formatted[] = $key . ': ' . $value;
        }

        return $formatted;
    }
}
