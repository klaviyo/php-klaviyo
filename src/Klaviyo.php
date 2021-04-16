<?php

namespace Klaviyo;

use Klaviyo\Exception\KlaviyoException as KlaviyoException;
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
     * @var string
     */
    const VERSION = '2.3.0';

    /**
     * Constructor for Klaviyo.
     *
     * @param string $private_key Private API key for Klaviyo account
     * @param string $public_key Public API key for Klaviyo account
     */
    public function __construct($private_key, $public_key)
    {
        $this->private_key = $private_key;
        $this->public_key = $public_key;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->private_key;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->public_key;
    }

    /**
     * Dynamically retrieve the corresponding API service and
     * save as property for re-use.
     *
     * @param string $api API service
     */
    public function __get($api)
    {
        $service = __NAMESPACE__ . '\\' . ucfirst($api);

        if (class_exists($service)) {
            $this->$api = new $service($this->public_key, $this->private_key);

            return $this->$api;
        }

        throw new KlaviyoException('Sorry, ' . $api . ' is not a valid Klaviyo API.');
    }
}
