<?php

use Klaviyo\KlaviyoAPI;
use Klaviyo\Profiles;
use getProfileWithID;
use PHPUnit\Framework\TestCase;

class ProfilesTest extends TestCase
{

    function testGetProfile()
    {
        $profiles = $this
            ->getMockBuilder('Klaviyo\Profiles')
            ->disableOriginalConstructor()
            ->onlyMethods(array('v1Request', 'getProfile'))
            ->getMockForAbstractClass();

        $profiles->method('getProfile')
            ->willReturn($this->getProfileWithID());
        $this->assertIsObject($profiles->getProfile('test_id'));
        $this->assertObjectHasAttribute('id', $profiles->getProfile('test_id'));
    }

    function getProfileWithID()
    {
        return json_decode(
            '{
                "updated":"2020-09-15 20:57:34",
                "last_name":"test",
                "$longitude":"",
                "$email":"test@test.com",
                "object":"person",
                "$latitude":"",
                "$address1":"",
                "$address2":"",
                "$title":"",
                "$timezone":"",
                "id":"test_id",
                "first_name":"test",
                "$organization":"",
                "$region":"",
                "$id":"",
                "created":"2019-10-22 20:12:36",
                "$last_name":"test",
                "$country":"",
                "$first_name":"test",
                "$city":""
                }'
        );
    }
}
