<?php


namespace Klaviyo;

use GuzzleHttp\Client;
use Klaviyo\Model\ProfileModel;
use Klaviyo\Model\EventModel;
use phpDocumentor\Reflection\DocBlock\Tags\Example;
use phpDocumentor\Reflection\DocBlock\Tags\Method;


class Metrics extends KlaviyoAPI
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

    /**
     * Returns a list of all metrics in Klaviyo
     * @link https://www.klaviyo.com/docs/api/metrics#metrics
     *
     * @param int $page defaults to 0,
     * For pagination, which page of results to return, defaults to 0
     *
     * @param int $count defaults to 50,
     * For pagination, the number of results to return, The maximum number of results per page is 100
     *
     * @return Client response object
     */
    public function getMetrics( int $page = null, int $count = null )
    {
        $params = $this->filterParams( array(
            self::PAGE=> $page,
            self::COUNT => $count
        ) );

        return $this->v1Request( self::METRICS, $params );
    }

    /**
     * Returns a list batched timeline of all events in your Klaviyo account
     * @link https://www.klaviyo.com/docs/api/metrics#metrics-timeline
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
    public function getTimeline( $since = null, string $uuid = null, int $count = null, string $sort = null )
    {
        $params = $this->setSinceParameter( $since, $uuid );

        $params = $this->filterParams( array_merge(
            $params,
            array(
                self::COUNT => $count,
                self::SORT => $sort
            )
        ) );

        $path = sprintf( '%s/%s', self::METRICS, self::TIMELINE );

        return $this->v1Request($path, $params );

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
     *
     */
    public function getMetricTimeline( string $metricID, $since = null, $uuid = null, int $count = null, string $sort = null)
    {
        $params = $this->setSinceParameter( $since, $uuid );

        $params = $this->filterParams( array_merge(
            $params,
            array(
                self::COUNT => $count,
                self::SORT => $sort
            )
        ) );

        $path = sprintf( '%s/%s/%s', self::METRIC, $metricID, self::TIMELINE );

        return $this->v1Request( $path, $params );
    }

    /**
     * Export event data from Klaviyo optionally filtering and segmented on available event properties.
     * @link https://www.klaviyo.com/docs/api/metrics#metric-export
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
        $params = $this->filterParams(
            array(
                self::START_DATE => $start_date,
                self::END_DATE => $end_date,
                self::UNIT => $unit,
                self::MEASUREMENT => $measurement,
                self::WHERE => $where,
                self::BY => $by,
                self::COUNT => $count
            )
        );

        $path = sprintf('%s/%s/%s', self::METRIC, $metricID, self::EXPORT );

        return $this->v1Request( $path, $params );

    }

}
