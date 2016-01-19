<?php

class KlaviyoException extends Exception { }

class Klaviyo {
    public $api_key;
    public $host = 'https://a.klaviyo.com/';

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
        $properties[$this->TRACK_ONCE_KEY] = true;
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

    function list_member_add($list_id, $email, $properties = array(), $confirm_optin = true) {
        $params = [
            'email' => $email,
            'properties' => json_encode($properties),
            'confirm_optin' => json_encode($confirm_optin),
        ];
        return $this->make_post_request(sprintf('api/v1/list/%s/members', $list_id), $params);
    }

    protected function build_params($params) {
        return 'data=' . urlencode(base64_encode(json_encode($params)));
    }

    protected function make_request($path, $params) {
        $url = $this->host . $path . '?' . $params;
        $response = file_get_contents($url);
        return $response == '1';
    }
    
    protected function make_post_request($path, $params = array())
    {
        $url = sprintf('%s%s', $this->host, $path);
        $params['api_key'] = $this->api_key;

        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($params),
            ),
        );
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === FALSE) {
            throw new \KlaviyoException();
        }

        return json_decode($response, true);
    }
};

?>