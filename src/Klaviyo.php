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
        if ((!array_key_exists('email', $properties) || empty($properties['email']))
            || (!array_key_exists('list_id', $properties) || empty($properties['list_id']))) {
            
            throw new KlaviyoException('You must identify a user by email and a list by ID.');
        }
        $params = array(
            'email' => $properties['email']
        );
        $response = $this->make_request('list/'.$properties['list_id'].'/members', $params);
        return $response->total > 0;
    }

    function add_person_to_list($properties) {
        if ((!array_key_exists('email', $properties) || empty($properties['email']))
            || (!array_key_exists('list_id', $properties) || empty($properties['list_id']))) {
            
            throw new KlaviyoException('You must identify a user by email and a list by ID.');
        }        
        $params = array(
            'email' => $properties['email'],
            'method' => 'post',
            'confirm_optin' => array_key_exists('confirm_optin', $properties) && $properties['confirm_optin'] ? 'true' : 'false',
            'properties' => json_encode(array(
                '$first_name' => array_key_exists('first_name', $properties) ? $properties['first_name'] : "",
                '$last_name' => array_key_exists('last_name', $properties) ? $properties['last_name'] : "",
                '$phone_number' => array_key_exists('phone_number', $properties) ? $properties['phone_number'] : "",
                '$zip' => array_key_exists('zip', $properties) ? $properties['zip'] : ""
            ))
        );
        $response = $this->make_request('list/'.$properties['list_id'].'/members', $params);
    }

    protected function build_params($params) {
        $params['api_key'] = $this->api_key;
        return urldecode(http_build_query($params));
    }

    protected function make_request($path, $params) {
        $url = $this->host . $path;
        $param_str = $this->build_params($params);
        if(array_key_exists("method", $params) && $params["method"] == "post") {
            // POST the content
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param_str);
            print_r($param_str);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
        }
        else {
            // GET the content
            $get_url = $url . '?' . $param_str;
            $response = file_get_contents($get_url);
        }
        return json_decode($response);
    }
};

?>