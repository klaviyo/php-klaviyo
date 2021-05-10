<?php

declare(strict_types=1);

namespace Klaviyo;

use Klaviyo\Model\EventModel;
use Klaviyo\Model\ProfileModel;

class PublicAPI
{
    /**
     * Track Class constants
     */
    const TRACK = 'track';
    const IDENTIFY = 'identify';

    private KlaviyoAPI $klaviyoAPI;

    public function __construct(KlaviyoAPI $klaviyoAPI)
    {
        $this->klaviyoAPI = $klaviyoAPI;
    }

    /**
     * The main Events API endpoint is /api/track, which is used to track when someone takes an action or does something
     * @link https://www.klaviyo.com/docs#track
     *
     * @param EventModel $event
     * @return array
     */
    public function track(EventModel $event) : array
    {
        $options = [KlaviyoAPI::QUERY => $event->toArray()];

        return $this->klaviyoAPI->publicRequest(self::TRACK, $options);
    }

    /**
     * The Identify API endpoint is /api/identify, which is used to track properties about an individual without tracking an associated event.
     * @link https://www.klaviyo.com/docs#identify
     *
     * @param ProfileModel $profile
     * @return array
     */
    public function identify(ProfileModel $profile) : array
    {
        $options = [
            KlaviyoAPI::QUERY => [
                KlaviyoAPI::PROPERTIES => $profile,
            ],
        ];

        return $this->klaviyoAPI->publicRequest(self::IDENTIFY, $options);
    }
}
