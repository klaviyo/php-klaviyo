<?php

class KlaviyoException extends Exception { }

class Klaviyo {
    public $api_key;
    public $host = 'https://a.klaviyo.com/api/v1/';

    protected $TRACK_ONCE_KEY = '__track_once__';
    
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }
    
    function track($event, $customer_properties=array(), $properties=array(), $timestamp=NULL) {
        if ((!array_key_exists('$email', $customer_properties) || empty($customer_properties['$email']))
            && (!array_key_exists('$id', $customer_properties) || empty($customer_properties['$id']))) {
            
            throw new KlaviyoException('You must identify a user by email or ID.');
        }

        $params = array(
            'event' => $event,
            'properties' => $properties,
            'customer_properties' => $customer_properties
        );

        if (!is_null($timestamp)) {
            $params['time'] = $timestamp;
        }

        return $this->make_request('track', $params);
    }

    function track_once($event, $customer_properties=array(), $properties=array(), $timestamp=NULL) {
        $properties[$this->TRACK_ONCE_KEY] = true;
        return $this->track($event, $customer_properties, $properties, $timestamp);
    }

    function identify($properties) {
        if ((!array_key_exists('$email', $properties) || empty($properties['$email']))
            && (!array_key_exists('$id', $properties) || empty($properties['$id']))) {
            
            throw new KlaviyoException('You must identify a user by email or ID.');
        }

        $params = array(
            'properties' => $properties
        );

        return $this->make_request('identify', $params);
    }

    function email_is_in_list($properties) {
        if ((!array_key_exists('$email', $properties) || empty($properties['$email']))
            || (!array_key_exists('$list_id', $properties) || empty($properties['$list_id']))) {
            
            throw new KlaviyoException('You must identify a user by email and a list by ID.');
        }
        $params = array(
            'email' => $properties['$email']
        );
        $response = $this->make_request('list/'.$properties['$list_id'].'/members', $params);
        return $response->total > 0;
    }

    protected function build_params($params) {
        $params['api_key'] = $this->api_key;
        return urldecode(http_build_query($params));
    }

    protected function make_request($path, $params) {
        $param_str = $this->build_params($params);
        $url = $this->host . $path . '?' . $param_str;
        $response = file_get_contents($url);
        return json_decode($response);
    }
};

?>