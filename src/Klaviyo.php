<?php

declare(strict_types=1);

namespace Klaviyo;

class Klaviyo
{
    private KlaviyoAPI $klaviyoAPI;

    const VERSION = '2.3.0';

    /**
     * Constructor for Klaviyo.
     *
     * @param string $private_key Private API key for Klaviyo account
     * @param string $public_key Public API key for Klaviyo account
     */
    public function __construct(string $private_key, string $public_key)
    {
        $this->klaviyoAPI = new KlaviyoAPI($public_key, $private_key);
    }

    public function metrics() : Metrics
    {
        return new Metrics($this->klaviyoAPI);
    }

    public function lists() : Lists
    {
        return new Lists($this->klaviyoAPI);
    }

    public function profiles() : Profiles
    {
        return new Profiles($this->klaviyoAPI);
    }

    public function publicAPI() : PublicAPI
    {
        return new PublicAPI($this->klaviyoAPI);
    }
}
