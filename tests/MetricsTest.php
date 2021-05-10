<?php

declare(strict_types=1);

namespace Klaviyo;

use Klaviyo\Exception\KlaviyoException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class MetricsTest extends TestCase
{
    /**
     * @var MockObject|\Klaviyo\KlaviyoAPI|mixed
     */
    private $klaviyoAPI;

    protected function setUp() : void
    {
        $this->klaviyoAPI = $this->createMock('Klaviyo\KlaviyoAPI');

        parent::setUp();
    }

    public function test_get_metrics()
    {
        $this->klaviyoAPI->method('v1Request')->willReturn(
            json_decode(
                '{
                      "end": 1,
                      "object": "$list",
                      "page_size": 50,
                      "start": 0,
                      "total": 1,
                      "data": [
                        {
                          "updated": "2014-11-03 20:54:40",
                          "name": "Added integration",
                          "created": "2014-11-03 20:54:40",
                          "object": "metric",
                          "id": "8qYK7L",
                          "integration": {
                            "category": "API",
                            "object": "integration",
                            "id": "4qYGmQ",
                            "name": "API"
                          }
                        }
                      ]
                  }',
                true
            )
        );
        $metrics = new Metrics($this->klaviyoAPI);

        $result = $metrics->getMetrics();

        self::assertIsArray($result);
        self::assertEquals(0, $result['start']);
        self::assertEquals(1, $result['total']);
    }

    public function test_get_metrics_fail_with_over_100_count_provided()
    {
        $this->klaviyoAPI->method('v1Request')->willReturn([]);
        $metrics = new Metrics($this->klaviyoAPI);

        $this->expectExceptionObject(new KlaviyoException('Current maximum count can not exceed 100'));
        $metrics->getMetrics(0, 101);
    }

    public function test_get_metrics_timeline()
    {
        $this->klaviyoAPI->method('v1Request')->willReturn(
            json_decode(
                '{
                    "count": 1,
                    "object": "$list",
                    "data": [
                      {
                        "object": "event",
                        "id": "3EJ9cW",
                        "person": {
                          "id": "5ta2Hr",
                          "$first_name": "George",
                          "$last_name": "Washington",
                          "$email": "george.washington@example.com"
                        },
                        "event_name": "Placed Order",
                        "statistic_id": "4Q8Y6N",
                        "timestamp": "1400656845",
                        "next": "31268980-edcb-11e3-8001-5b3d8e19a1ac",
                        "event_properties": {
                          "$extra": {
                            "TotalTax": 0,
                            "TotalDiscount": 0,
                            "TotalShipping": 6,
                            "Items": [
                              {
                                "Description": null,
                                "Price": 29,
                                "Slug": "woodtooth",
                                "Quantity": 1,
                                "LineTotal": 29,
                                "ProductID": "537c06c610bcf70400540c81",
                                "Name": "Wooden Tooth"
                              }
                            ]
                          },
                          "$value": 35,
                          "IsDiscounted": false,
                          "UsedCoupon": false
                        }
                      }
                    ]
                    }',
                true
            )
        );
        $metrics = new Metrics($this->klaviyoAPI);

        $result = $metrics->getMetricsTimeline();
        self::assertIsArray($result);
        self::assertEquals(1, $result['count']);
        self::assertIsArray($result['data']);

        $result = $metrics->getMetricsTimeline('20200101', null);
        self::assertIsArray($result);
        self::assertEquals(1, $result['count']);
        self::assertIsArray($result['data']);

        $result = $metrics->getMetricsTimeline('20200101', '30d5018c-6106-4715-a238-0028644efa1c');
        self::assertIsArray($result);
        self::assertEquals(1, $result['count']);
        self::assertIsArray($result['data']);
    }

    public function test_get_metric_timeline()
    {
        $this->klaviyoAPI->method('v1Request')->willReturn(
            json_decode(
                '{
                    "count": 1,
                    "object": "$list",
                    "data": [
                      {
                        "object": "event",
                        "id": "3EJ9cW",
                        "person": {
                          "id": "5ta2Hr",
                          "$first_name": "George",
                          "$last_name": "Washington",
                          "$email": "george.washington@example.com"
                        },
                        "event_name": "Placed Order",
                        "statistic_id": "4Q8Y6N",
                        "timestamp": "1400656845",
                        "next": "31268980-edcb-11e3-8001-5b3d8e19a1ac",
                        "event_properties": {
                          "$extra": {
                            "TotalTax": 0,
                            "TotalDiscount": 0,
                            "TotalShipping": 6,
                            "Items": [
                              {
                                "Description": null,
                                "Price": 29,
                                "Slug": "woodtooth",
                                "Quantity": 1,
                                "LineTotal": 29,
                                "ProductID": "537c06c610bcf70400540c81",
                                "Name": "Wooden Tooth"
                              }
                            ]
                          },
                          "$value": 35,
                          "IsDiscounted": false,
                          "UsedCoupon": false
                        }
                      }
                    ]
                    }',
                true
            )
        );
        $metrics = new Metrics($this->klaviyoAPI);

        $result = $metrics->getMetricTimeline('123123');
        self::assertIsArray($result);
        self::assertEquals(1, $result['count']);
        self::assertIsArray($result['data']);

        $result = $metrics->getMetricTimeline('123123', '20200101', null);
        self::assertIsArray($result);
        self::assertEquals(1, $result['count']);
        self::assertIsArray($result['data']);

        $result = $metrics->getMetricTimeline('123123', '20200101', '30d5018c-6106-4715-a238-0028644efa1c');
        self::assertIsArray($result);
        self::assertEquals(1, $result['count']);
        self::assertIsArray($result['data']);
    }
}
