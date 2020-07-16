<?php

namespace Klaviyo;

use Klaviyo\Model\EventModel;
use Klaviyo\Model\ProfileModel;

class PublicAPI extends KlaviyoAPI
{
    /**
     * Track Class constants
     */
    const TRACK = 'track';
    const IDENTIFY = 'identify';

    /**
     * PublicAPI constructor.
     * @param $public_key
     * @param $private_key
     * @param string $host
     */
    public function __construct( $public_key, $private_key, $host = self::BASE_URL )
    {
        parent::__construct( $public_key, $private_key, $host = self::BASE_URL );
    }

    /**
     * The main Events API endpoint is /api/track, which is used to track when someone takes an action or does something
     * @link https://www.klaviyo.com/docs#track
     *
     * @param EventModel $event
     * @return mixed
     */
    public function track( EventModel $event )
    {
        $options = array( self::QUERY => $event->toArray() );

        return $this->publicRequest( self::TRACK, $options );
    }

    /**
     * The Identify API endpoint is /api/identify, which is used to track properties about an individual without tracking an associated event.
     * @link https://www.klaviyo.com/docs#identify
     *
     * @param ProfileModel $profile
     * @return mixed
     */
    public function identify( ProfileModel $profile )
    {
        $options = array(
            self::QUERY => array(
                self::PROPERTIES => $profile
            )
        );

        return $this->publicRequest( self::IDENTIFY, $options );
    }
}
