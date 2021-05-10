<?php

declare(strict_types=1);

namespace Klaviyo;

class Profiles
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

    private KlaviyoAPI $klaviyoAPI;

    public function __construct(KlaviyoAPI $klaviyoAPI)
    {
        $this->klaviyoAPI = $klaviyoAPI;
    }

    /**
     * Retrieve all data attributes for a person, based on the Klaviyo personID
     * @link https://www.klaviyo.com/docs/api/people#person
     *
     * @param string $personId 6 digit unique identifier for Profiles
     * @return array
     */
    public function getProfile(string $personId) : array
    {
        $path = sprintf('%s/%s', self::PERSON, $personId);
        return $this->klaviyoAPI->v1Request($path);
    }

    /**
     * Add or update one or more attributes for a Person, based on the Klaviyo person ID.
     * If a property already exists, it will be updated. If a property is not set for that record, it will be created
     * @link https://www.klaviyo.com/docs/api/people#person
     *
     * @param string $personId 6 digit unique identifier for Profile
     * @param array $properties In addition to these pre-defined Klaviyo arguments, you may pass any arbitrary key/value pair as a custom property. The names of the custom properties cannot start with $.
     *
     * @return array
     */
    public function updateProfile(string $personId, array $properties) : array
    {
        $path = sprintf('%s/%s', self::PERSON, $personId);
        return $this->klaviyoAPI->v1Request($path, $properties, KlaviyoAPI::HTTP_PUT);
    }

    /**
     * Returns a batched timeline of all events for a person.
     * @link https://www.klaviyo.com/docs/api/people#metrics-timeline
     *
     * @param string $personId 6 digit unique identifier for Profile
     * @param string|null $since To be used with the since argument of the call, can accept UNIX timestamps,The `since` argument of the call defaults to current time
     * @param string|null $uuid Can be used with the `since` argument of the call, is obtained via the 'next' attribute of a prior API call. The `since` argument of the call defaults to current time
     * @param int $count defaults to 100, Number of events to return in a batch
     * @param string $sort defaults to 'desc' Sort order to apply to timeline
     *
     * @return array
     */
    public function getAllProfileMetricsTimeline(string $personId, ?string $since = null, ?string $uuid = null, int $count = 100, string $sort = 'desc') : array
    {
        $params = $this->klaviyoAPI->setSinceParameter($since, $uuid);

        $params = $this->klaviyoAPI->filterParams(array_merge(
            $params,
            [
                KlaviyoAPI::COUNT => $count,
                KlaviyoAPI::SORT => $sort,
            ]
        ));

        $path = sprintf('%s/%s/%s/%s', self::PERSON, $personId, self::METRICS, self::TIMELINE);

        return $this->klaviyoAPI->v1Request($path, $params);
    }

    /**
     * Returns a person's batched timeline for one specific event type.
     * @link https://www.klaviyo.com/docs/api/people#metric-timeline
     *
     * @param string $personId 6 digit unique identifier for Profile
     * @param string $metricId 6 digit unique identifier of the Metric
     * @param string|null $since To be used with the since argument of the call, can accept UNIX timestamps, The `since` argument of the call defaults to current time
     * @param string|null $uuid Can be used with the `since` argument of the call, is obtained via the 'next' attribute of a prior API call. The `since` argument of the call defaults to current time
     * @param int $count defaults to 100, Number of events to return in a batch
     * @param string $sort defaults to 'desc' Sort order to apply to timeline
     *
     * @return array
     */
    public function getProfileMetricTimeline(string $personId, string $metricId, ?string $since = null, ?string $uuid = null, int $count = 100, $sort = 'desc') : array
    {
        $params = $this->klaviyoAPI->setSinceParameter($since, $uuid);

        $params = $this->klaviyoAPI->filterParams(array_merge(
            $params,
            [
                KlaviyoAPI::COUNT => $count,
                KlaviyoAPI::SORT => $sort,
            ]
        ));

        $path = sprintf('%s/%s/%s/%s/%s', self::PERSON, $personId, self::METRIC, $metricId, self::TIMELINE);

        return $this->klaviyoAPI->v1Request($path, $params);
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
