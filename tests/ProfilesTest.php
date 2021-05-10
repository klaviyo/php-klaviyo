<?php

declare(strict_types=1);

namespace Klaviyo;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ProfilesTest extends TestCase
{
    /**
     * @var \Klaviyo\KlaviyoAPI|mixed|MockObject
     */
    private $klaviyoAPI;

    protected function setUp() : void
    {
        $this->klaviyoAPI = $this->createMock('Klaviyo\KlaviyoAPI');

        parent::setUp();
    }

    public function test_get_profile()
    {
        $this->klaviyoAPI->method('v1Request')->willReturn(
            json_decode(
                '{
                    "object": "person",
                    "id": "dqQnNW",
                    "$email": "george.washington@example.com",
                    "$first_name": "George",
                    "$last_name": "Washington",
                    "$organization": "U.S. Government",
                    "$title": "President",
                    "$city": "Mount Vernon",
                    "$region": "Virginia",
                    "$zip": "22121",
                    "$country": "United States",
                    "$timezone": "US/Eastern",
                    "$phone_number": ""
                }',
                true,
                512,
                JSON_THROW_ON_ERROR
            )
        );
        $profiles = new Profiles($this->klaviyoAPI);

        $profile = $profiles->getProfile('dqQnNW');

        self::assertIsArray($profile);
        self::assertEquals('dqQnNW', $profile['id']);
    }

    public function test_updating_profile()
    {
        $this->klaviyoAPI->method('v1Request')->willReturn(
            json_decode(
                '{
                    "object": "person",
                    "id": "dqQnNW",
                    "$email": "george.washington@example.com",
                    "$first_name": "John",
                    "$last_name": "Washington",
                    "$organization": "U.S. Government",
                    "$title": "President",
                    "$city": "Mount Vernon",
                    "$region": "Virginia",
                    "$zip": "22121",
                    "$country": "United States",
                    "$timezone": "US/Eastern",
                    "$phone_number": ""
                }',
                true,
                512,
                JSON_THROW_ON_ERROR
            )
        );
        $profiles = new Profiles($this->klaviyoAPI);

        $profile = $profiles->updateProfile('dqQnNW', ['$first_name' => 'John']);

        self::assertIsArray($profile);
        self::assertEquals('dqQnNW', $profile['id']);
        self::assertEquals('John', $profile['$first_name']);
    }

    public function test_listing_person_complete_timeline()
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
                true,
                512,
                JSON_THROW_ON_ERROR
            )
        );
        $profiles = new Profiles($this->klaviyoAPI);

        $timeline = $profiles->getAllProfileMetricsTimeline('3EJ9cW');

        self::assertIsArray($timeline);
        self::assertEquals(1, $timeline['count']);
        self::assertIsArray($timeline['data']);
    }

    public function test_listing_person_complete_timeline_for_particular_metric()
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
                true,
                512,
                JSON_THROW_ON_ERROR
            )
        );
        $profiles = new Profiles($this->klaviyoAPI);

        $timeline = $profiles->getProfileMetricTimeline('3EJ9cW', '4Q8Y6N');

        self::assertIsArray($timeline);
        self::assertEquals(1, $timeline['count']);
        self::assertIsArray($timeline['data']);
    }
}
