<?php

namespace Klaviyo;

/**
 * Main class for accessing the Klaviyo API
 */
class Klaviyo
{
    /**
     * @var string
     */
    protected $private_key;

    /**
     * @var string
     */
    protected $public_key;
    
    /**
     * Constructor for Klaviyo.
     *
     * @param string $private_key Private API key for Klaviyo account
     */
    public function __construct( $private_key ) {
        $this->private_key = $private_key;
    }

    /**
     * Dynamically retrieve the corresponding API service and
     * save as property for re-use.
     *
     * @param string $api API service
     */
    public function __get( $api ) {
        $service = __NAMESPACE__ . '\\' . ucfirst( $api );

        if ( class_exists( $service ) ) {
            $this->$api = new $service( $this->public_key, $this->private_key );

            return $this->$api;
        }

        throw new KlaviyoException('Sorry, ' . $api . ' is not a valid Klaviyo API.');
    }

    public function setPublicApiKey( $key ) {
        $this->public_key = $key;
    }

    public function getPublicApiKey() {
        return $this->public_key;
    }
}
