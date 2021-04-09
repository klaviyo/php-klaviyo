<?php


namespace Klaviyo;

class Profiles extends KlaviyoAPI
{
    /**
     * Profiles endpoint constants
     */
    const PERSON = 'person';
    const METRICS = 'metrics';
    const METRIC = 'metric';
    const PEOPLE = 'people';
    const SEARCH = 'search';
    const TIMELINE = 'timeline';

    /**
     * Retrieve all data attributes for a person, based on the Klaviyo personID
     * @link https://www.klaviyo.com/docs/api/people#person
     *
     * @param $personId
     * 6 digit unique identifier for Profiles
     *
     * @return bool|mixed
     */
    public function getProfile( $personId )
    {
        $path = sprintf( '%s/%s', self::PERSON, $personId );
        return $this->v1Request( $path );
    }

    /**
     * Add or update one or more attributes for a Person, based on the Klaviyo person ID.
     * If a property already exists, it will be updated. If a property is not set for that record, it will be created
     * @link https://www.klaviyo.com/docs/api/people#person
     *
     * @param $personId
     * 6 digit unique identifier for Profile
     *
     * @param array $properties
     * In addition to these pre-defined Klaviyo arguments, you may pass any arbitrary key/value pair as a custom property. The names of the custom properties cannot start with $.
     *
     * @return bool|mixed
     */
    public function updateProfile( $personId, $properties )
    {
        $path = sprintf( '%s/%s', self::PERSON, $personId );
        return $this->v1Request( $path, $properties, self::HTTP_PUT );
    }

    /**
     * Returns a batched timeline of all events for a person.
     * @link https://www.klaviyo.com/docs/api/people#metrics-timeline
     *
     * @param $personId
     * 6 digit unique identifier for Profile
     *
     * @param string $since
     * To be used with the since argument of the call, can accept UNIX timestamps,
     * The `since` argument of the call defaults to current time
     *
     * @param string $uuid
     * Can be used with the `since` argument of the call, is obtained via the 'next' attribute of a prior API call.
     * The `since` argument of the call defaults to current time
     *
     * @param int $count defaults to 100,
     * Number of events to return in a batch
     *
     * @param string $sort defaults to 'desc'
     * Sort order to apply to timeline
     *
     * @return bool|mixed
     */
    public function getAllProfileMetricsTimeline( $personId, $since = null, $uuid = null, $count = null, $sort = null )
    {
        $params = $this->setSinceParameter( $since, $uuid );

        $params = $this->filterParams( array_merge(
            $params,
            array(
                self::COUNT => $count,
                self::SORT => $sort
            )
        ) );

        $path = sprintf( '%s/%s/%s/%s', self::PERSON, $personId, self::METRICS, self::TIMELINE );

        return $this->v1Request( $path, $params );
    }

    /**
     * Returns a person's batched timeline for one specific event type.
     * @link https://www.klaviyo.com/docs/api/people#metric-timeline
     *
     * @param $personId
     * 6 digit unique identifier for Profile
     *
     * @param $metricId
     * 6 digit unique identifier of the Metric
     *
     * @param string $since
     * To be used with the since argument of the call, can accept UNIX timestamps,
     * The `since` argument of the call defaults to current time
     *
     * @param string $uuid
     * Can be used with the `since` argument of the call, is obtained via the 'next' attribute of a prior API call.
     * The `since` argument of the call defaults to current time
     *
     * @param int $count defaults to 100,
     * Number of events to return in a batch
     *
     * @param string $sort defaults to 'desc'
     * Sort order to apply to timeline
     *
     * @return bool|mixed
     */
    public function getProfileMetricTimeline( $personId, $metricId, $since = null, $uuid = null, $count = null, $sort = null )
    {
        $params = $this->setSinceParameter( $since, $uuid );

        $params = $this->filterParams( array_merge(
            $params,
            array(
                self::COUNT => $count,
                self::SORT => $sort
            )
        ) );

        $path = sprintf('%s/%s/%s/%s/%s', self::PERSON, $personId, self::METRIC, $metricId, self::TIMELINE );

        return $this->v1Request( $path, $params );
    }

    /**
     * Get ID of profile by email address.
     *
     * @param string $email Email address of desired profile.
     * @return mixed
     * @throws Exception\KlaviyoException
     */
    public function getProfileIdByEmail($email)
    {
        $params = $this->createRequestBody([self::EMAIL => $email]);
        $path = sprintf('%s/%s', self::PEOPLE, self::SEARCH);

        return $this->v2Request($path, $params);
    }
}
