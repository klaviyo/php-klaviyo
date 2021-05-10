<?php

declare(strict_types=1);

namespace Klaviyo;

use Klaviyo\Exception\KlaviyoException;

class Metrics
{
    /**
     * Metrics endpoint constants
     */
    const METRICS = 'metrics';
    const METRIC = 'metric';
    const TIMELINE = 'timeline';
    const EXPORT = 'export';

    /**
     * Metrics API arguments
     */
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const UNIT = 'unit';
    const WHERE = 'where';
    const MEASUREMENT = 'measurement';
    const BY = 'by';

    private KlaviyoAPI $klaviyoAPI;

    public function __construct(KlaviyoAPI $klaviyoAPI)
    {
        $this->klaviyoAPI = $klaviyoAPI;
    }

    /**
     * Returns a list of all metrics in Klaviyo
     * @link https://www.klaviyo.com/docs/api/metrics#metrics
     *
     * @param int $page For pagination, which page of results to return, defaults to 0
     * @param int $count For pagination, the number of results to return, The maximum number of results per page is 100, defaults to 50
     *
     * @return array
     */
    public function getMetrics(int $page = 0, int $count = 50) : array
    {
        if ($count > 100) {
            throw new KlaviyoException('Current maximum count can not exceed 100');
        }

        $params = $this->klaviyoAPI->filterParams(
            [
                KlaviyoAPI::PAGE => $page,
                KlaviyoAPI::COUNT => $count,
            ]
        );

        return $this->klaviyoAPI->v1Request(self::METRICS, $params);
    }

    /**
     * Returns a list batched timeline of all events in your Klaviyo account
     * @link https://www.klaviyo.com/docs/api/metrics#metrics-timeline
     *
     * @param string|null $since To be used with the since argument of the call, can accept UNIX timestamps, The `since` argument of the call defaults to current time
     * @param string|null $uuid Can be used with the `since` argument of the call, is obtained via the 'next' attribute of a prior API call. The `since` argument of the call defaults to current time
     * @param int $count defaults to 100, Number of events to return in a batch
     * @param string $sort defaults to 'desc', Sort order to apply to timeline
     *
     * @return array
     */
    public function getMetricsTimeline(
        ?string $since = null,
        ?string $uuid = null,
        int $count = 100,
        string $sort = 'desc'
    ) : array {
        $params = [
            KlaviyoAPI::COUNT => $count,
            KlaviyoAPI::SORT => $sort,
        ];

        if ($since || $uuid) {
            $params = array_merge($params, $this->klaviyoAPI->setSinceParameter($since, $uuid));
        }

        $path = sprintf('%s/%s', self::METRICS, self::TIMELINE);

        return $this->klaviyoAPI->v1Request($path, $params);
    }

    /**
     * Returns a batched timeline for one specific type of metric, requires metric ID from your Klaviyo account
     *
     * @deprecated 2.2.6
     * @see getMetricTimelineById
     *
     * @param string $metricID
     * 6 digit unique identifier of the metric
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
     *
     */
    public function getMetricTimeline( $metricID, $since = null, $uuid = null, $count = null, $sort = null )
    {
        return $this->getMetricTimelineById($metricID, $since, $uuid, $count, $sort);
    }

    /**
     * Returns a batched timeline for one specific type of metric, requires metric ID from your Klaviyo account
     * @link https://www.klaviyo.com/docs/api/metrics#metric-timeline
     * @param string $metricID
     * 6 digit unique identifier of the metric
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
    public function getMetricTimelineById($metricID, $since = null, $uuid = null, $count = null, $sort = null)
    {
        $params = $this->setSinceParameter($since, $uuid);

        $params = $this->filterParams(
            array_merge(
                $params,
                array(
                    self::COUNT => $count,
                    self::SORT => $sort
                )
            )
        );

        $path = sprintf('%s/%s/%s', self::METRIC, $metricID, self::TIMELINE);
        return $this->v1Request($path, $params);
    }

    /**
     * Export event data from Klaviyo optionally filtering and segmented on available event properties.
     *
     * @deprecated 2.2.6
     * @see getMetricExport
     *
     * @param string $metricID
     * 6 digit unique identifier of the metric
     *
     * @param $start_date
     *
     * @param $end_date
     *
     * @param $unit
     *
     * @param $measurement
     *
     * @param $where
     *
     * @param $by
     *
     * @param $count
     *
     * @return bool|mixed
     * @throws KlaviyoException
     */
    public function exportMetricData( $metricID,
                                      $start_date = null,
                                      $end_date = null,
                                      $unit = null,
                                      $measurement = null,
                                      $where = null,
                                      $by = null,
                                      $count = null )
    {
        return $this->getMetricExport($metricID, $start_date, $end_date, $unit, $measurement, $where, $by, $count);
    }

    /**
     * Export event data from Klaviyo optionally filtering and segmented on available event properties.
     * @link https://www.klaviyo.com/docs/api/metrics#metric-export
     *
     * @param string $metricID 6 digit unique identifier of the metric
     * @param $start_date
     * @param $end_date
     * @param $unit
     * @param $measurement
     * @param $where
     * @param $by
     * @param $count
     *
     * @return bool|mixed
     * @throws KlaviyoException
     */
    public function getMetricExport(
        $metricID,
        $start_date = null,
        $end_date = null,
        $unit = null,
        $measurement = null,
        $where = null,
        $by = null,
        $count = null
    )
    {
        if (isset($where) && isset($by)) {
            throw new KlaviyoException('Please use either \'where\' or \'by\', only one of these variables can be set for the export call');
        }

        $params = $this->klaviyoAPI->filterParams(
            [
                self::START_DATE => $start_date,
                self::END_DATE => $end_date,
                self::UNIT => $unit,
                self::MEASUREMENT => $measurement,
                self::WHERE => $where,
                self::BY => $by,
                self::COUNT => $count,
            ]
        );

        $path = sprintf('%s/%s/%s', self::METRIC, $metricID, self::EXPORT);

        return $this->v1Request($path, $params);
    }
}
