<?php

namespace Klaviyo;

use Klaviyo\Model\EventModel;
use Klaviyo\Model\ProfileModel;

class Track extends KlaviyoAPI
{
    /**
     * Track Class constants
     */
    const TRACK = 'track';
    const IDENTIFY = 'identify';

    /**
     * Track constructor.
     * @param $public_key
     * @param $private_key
     * @param string $host
     */
    public function __construct($public_key, $private_key, $host = self::BASE_URL) {
        parent::__construct($public_key, $private_key, $host = self::BASE_URL);

        if ( !isset( $this->public_key ) ) {
            throw new KlaviyoException('Public key is not defined.');
        }
    }

    /**
     * The main Events API endpoint is /api/track, which is used to track when someone takes an action or does something
     * @link https://www.klaviyo.com/docs#track
     *
     * @param EventModel $event
     * @return mixed
     */
    public function trackEvent(EventModel $event ) {
        $options = [self::QUERY => $event->toArray()];
        
        return $this->publicRequest( self::TRACK, $options );
    }

    /**
     * The Identify API endpoint is /api/identify, which is used to track properties about an individual without tracking an associated event.
     * @link https://www.klaviyo.com/docs#identify
     *
     * @param ProfileModel $profile
     * @return mixed
     */
    public function identifyProfile (ProfileModel $profile ) {
        $options = [self::QUERY => [self::PROPERTIES => $profile]];

        return $this->publicRequest( self::IDENTIFY, $options );
    }
}
