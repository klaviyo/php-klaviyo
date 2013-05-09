<?php

class KlaviyoException extends Exception { }

class Klaviyo {
    public $api_key;
    public $host = 'http://a.klaviyo.com/';

    protected $TRACK_ONCE_KEY = '__track_once__';
    private   $ERROR_MESSAGE  = '';
    
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }
    
    function track($event, $customer_properties=array(), $properties=array(), $timestamp=NULL) {
        if ((!array_key_exists('$email', $customer_properties) || empty($customer_properties['$email']))
            && (!array_key_exists('$id', $customer_properties) || empty($customer_properties['$id']))) {
            
            throw new KlaviyoException('You must identify a user by email or ID.');
        }

        $params = array(
            'token' => $this->api_key,
            'event' => $event,
            'properties' => $properties,
            'customer_properties' => $customer_properties
        );

        if (!is_null($timestamp)) {
            $params['time'] = $timestamp;
        }

        $encoded_params = $this->build_params($params);
        return $this->make_request('api/track', $encoded_params);
    }

    function track_once($event, $customer_properties=array(), $properties=array(), $timestamp=NULL) {
        $properties[$TRACK_ONCE_KEY] = true;
        return $this->track($event, $customer_properties, $properties, $timestamp);
    }

    function identify($properties) {
        if ((!array_key_exists('$email', $properties) || empty($properties['$email']))
            && (!array_key_exists('$id', $properties) || empty($properties['$id']))) {
            
            throw new KlaviyoException('You must identify a user by email or ID.');
        }

        $params = array(
            'token' => $this->api_key,
            'properties' => $properties
        );

        $encoded_params = $this->build_params($params);
        return $this->make_request('api/identify', $encoded_params);
    }

    function get_error() {
        return $this->ERROR_MESSAGE;
    }

    protected function build_params($params) {
        return 'data=' . urlencode(base64_encode(json_encode($params)));
    }

    protected function make_request($path, $params) {
        $url = $this->host . $path . '?' . $params;
        $response = file_get_contents($url);
        if ($response == '1') {
            $this->ERROR_MESSAGE = ''; //Reset the error message in case someone wants to use empty() on it.
            return true; //Success
        } else {
            $this->ERROR_MESSAGE = $response; //Save the error message in case of a problem.
            return false; //Boolean to avoid breaking back-compatibility.
        }
    }
};

?>