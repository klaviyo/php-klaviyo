<?php

namespace Klaviyo;

use Klaviyo\Model\ProfileModel as profileModel;
use phpDocumentor\Reflection\Types\Self_;

/**
 * Class Lists
 * @package Klaviyo
 */
class Lists extends KlaviyoBase
{
    /**
     * List endpoint constants
     */
    const EXCLUSIONS = 'exclusions';
    const GROUP = 'group';
    const LIST = 'list';
    const LISTS = 'lists';
    const MEMBERS = 'members';
    const SEGMENT = 'segment';
    const SUBSCRIBE = 'subscribe';

    /**
     * Create a new list
     * @link https://www.klaviyo.com/docs/api/v2/lists#post-lists
     *
     * @param string $listName
     * Name of list to be created.
     *
     * @return bool|mixed
     */
    public function createList( $listName )
    {
        $options = $this->createOptions('list_name', $listName);

        return $this->v2Request( self::LISTS, $options, self::HTTP_POST );
    }

    /**
     * Get all lists
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-lists
     *
     * @return bool/mixed
     */
    public function getLists() {

        return $this->v2Request( self::LISTS );
    }

    /**
     * Get information about a list
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-list
     *
     * @param $listId
     * 6 digit unique identifier of the list
     *
     * @return bool|mixed
     */
    public function getListDetails($listId )
    {
        return $this->v2Request( self::LIST.'/'.$listId );
    }

    /**
     * Update a list's properties
     * @link https://www.klaviyo.com/docs/api/v2/lists#put-list
     *
     * @param $listId
     * 6 digit unique identifier of the list
     *
     * @param $list_name
     * String to update list name to
     *
     * @return bool|mixed
     */
    public function updateListDetails($listId, $list_name )
    {
        $params = $this->createRequestBody( array(
            'list_name' => $list_name
        ) );

        $path = self::LIST.'/'.$listId;

        return $this->v2Request( $path, $params, self::HTTP_PUT );
    }

    /**
     * Delete a list from an account. This is a destructive operation and cannot be undone. It will also remove flow triggers associated with the list.
     * @link https://www.klaviyo.com/docs/api/v2/lists#delete-list
     *
     * @param $listId
     * 6 digit unique identifier of the list
     *
     * @return bool|mixed
     */
    public function deleteList($listId )
    {
        return $this->v2Request( self::LIST.'/'.$listId, [], self::HTTP_DELETE );
    }

    /**
     * Subscribe or re-subscribe profiles to a list. Profiles will be single or double opted into the specified list in accordance with that list’s settings.
     * @link https://www.klaviyo.com/docs/api/v2/lists#post-subscribe
     *
     * @param $listId
     * 6 digit unique identifier of the list
     *
     * @param array $profiles
     * The profiles that you would like to subscribe. Each object in the list must have either an email or phone number key.
     * You can also provide additional properties as key-value pairs. If you are a GDPR compliant business, you will need to include $consent in your API call.
     * $consent is a Klaviyo special property and only accepts the following values: "email", "web", "sms", "directmail", "mobile".
     * If you are updating consent for a phone number or would like to send an opt-in SMS to the profile (for double opt-in lists), include an sms_consent key in the profile with a value of true or false.
     *
     * @return bool|mixed
     */
    public function subscribeMembersToList($listId, array $profiles )
    {
        array_walk($profiles, [$this, 'isInstanceOf'], __NAMESPACE__ . '\Model\ProfileModel');
        $profiles = array_map(
            function( $profile ) {
                return $profile->toArray();
            }, $profiles
        );

        $path = sprintf( '%s/%s/%s', self::LIST, $listId, self::SUBSCRIBE );
        $params = $this->createOptions( 'profiles', $profiles );

        return $this->v2Request( $path, $params, self::HTTP_POST );
    }

    /**
     * Check if profiles are on a list and not suppressed.
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-subscribe
     *
     * @param $listId
     * 6 digit unique identifier of the list
     *
     * @param array|null $emails
     * The emails corresponding to the profiles that you would like to check.
     *
     * @param array|null $phoneNumbers
     * The phone numbers corresponding to the profiles that you would like to check.
     * Phone numbers must be in E.164 format.
     *
     * @param array|null $pushTokens
     *The push tokens corresponding to the profiles that you would like to check.
     *
     * @return bool|mixed
     */
    public function checkListSubscriptions ($listId, array $emails = null, array $phoneNumbers = null, array $pushTokens = null )
    {
        $params = $this->createRequestJson(
            $this->filterParams(
                array(
                    'emails' => $emails,
                    'phone_numbers' => $phoneNumbers,
                    'push_tokens' => $pushTokens
                )
            )
        );

        $path = sprintf('%s/%s/%s', self::LIST, $listId, self::SUBSCRIBE );

        return $this->v2Request( $path, $params, self::HTTP_GET );
    }

    /**
     * Unsubscribe and remove profiles from a list.
     * @link https://www.klaviyo.com/docs/api/v2/lists#delete-subscribe
     *
     * @param string $listId
     * 6 digit unique identifier of the list
     *
     * @param array $emails
     * The emails corresponding to the profiles that you would like to check.
     *
     * @return bool|mixed
     */
    public function unsubscribeMembersFromList($listId, array $emails )
    {
        $params = $this->createRequestJson(
            $this->filterParams(
                array(
                    'emails' => $emails
                )
            )
        );

        $path = sprintf('%s/%s/%s', self::LIST, $listId, self::SUBSCRIBE );

        return $this->v2Request( $path, $params, self::HTTP_DELETE );
    }

    /**
     * Use this endpoint to add profiles to and remove profiles from Klaviyo lists without changing their subscription or suppression status.
     * @link https://www.klaviyo.com/docs/api/v2/lists#post-members
     *
     * @param $listId
     * 6 digit unique identifier of the list
     *
     * @param array $profiles
     * The profiles that you would like to subscribe. Each object in the list must have either an email or phone number key.
     * You can also provide additional properties as key-value pairs. If you are a GDPR compliant business, you will need to include $consent in your API call.
     * $consent is a Klaviyo special property and only accepts the following values: "email", "web", "sms", "directmail", "mobile".
     * If you are updating consent for a phone number or would like to send an opt-in SMS to the profile (for double opt-in lists), include an sms_consent key in the profile with a value of true or false.
     *
     * @return bool|mixed
     *
     */
    public function addMembersToList( $listId, array $profiles )
    {
        array_walk($profiles, [$this, 'isInstanceOf'], __NAMESPACE__ . '\Model\ProfileModel');
        $profiles = array_map(
            function( $profile ) {
                return $profile->toArray();
            }, $profiles
        );

        $path = sprintf( '%s/%s/%s', self::LIST, $listId, self::MEMBERS );
        $options = $this->createOptions( 'profiles', $profiles );

        return $this->v2Request( $path, $options, self::HTTP_POST );
    }

    /**
     * Check if profiles are on a list.
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-members
     *
     * @param $listId
     * 6 digit unique identifier of the list
     *
     * @param array|null $emails
     * The emails corresponding to the profiles that you would like to check.
     *
     * @param array|null $phoneNumbers
     * The phone numbers corresponding to the profiles that you would like to check.
     * Phone numbers must be in E.164 format.
     *
     * @param array|null $pushTokens
     *The push tokens corresponding to the profiles that you would like to check.
     *
     * @return bool|mixed
     */
    public function checkListMembership($listId, array $emails = null, array $phoneNumbers = null, array $pushTokens = null )
    {
        $params = $this->createRequestJson(
            $this->filterParams(
                array(
                    'emails' => $emails,
                    'phone_numbers' => $phoneNumbers,
                    'push_tokens' => $pushTokens
                )
            )
        );

        $path = sprintf('%s/%s/%s', self::LIST, $listId, self::MEMBERS );

        return $this->v2Request( $path, $params, self::HTTP_GET );
    }

    /**
     * Remove profiles from a list.
     * @link https://www.klaviyo.com/docs/api/v2/lists#delete-members
     *
     * @param string $listId
     * 6 digit unique identifier of the list.
     *
     * @param array $emails
     * The emails corresponding to the profiles that you would like to check.
     *
     * @return bool|mixed
     */
    public function removeMembersFromList($listId, array $emails )
    {
        {
            $params = $this->createRequestJson(
                $this->filterParams(
                    array(
                        'emails' => $emails
                    )
                )
            );

            $path = sprintf('%s/%s/%s', self::LIST, $listId, self::MEMBERS );

            return $this->v2Request( $path, $params, self::HTTP_DELETE );
        }
    }

    /**
     * Get all of the emails and phone numbers that have been excluded from a list along with the exclusion reasons and exclusion time.
     * This endpoint uses batching to return the records, so for a large list multiple calls will need to be made to get all of the records.
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-exclusions-all
     *
     * @param $listId
     * 6 digit unique identifier of the list
     *
     * @param int $marker
     * A marker value returned by a previous GET call. Use this to grab the next batch of records.
     *
     * @return bool|mixed
     */
    public function getAllExclusionsOnList( $listId, int $marker = null )
    {
        $params = $this->createRequestBody(
            $this->filterParams(
                array(
                    'marker' => $marker
                )
            )
        );

        $path = sprintf('%s/%s/%s/%s',self::LIST, $listId, self::EXCLUSIONS, 'all' );

        return $this->v2Request( $path, $params );
    }

    /**
     * Get all of the emails, phone numbers, and push tokens for profiles in a given list or segment.
     * This endpoint uses batching to return the records, so for a large list or segment multiple calls will need to be made to get all of the records.
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-members-all
     *
     * @param $groupId
     * 6 digit unique identifier of List/Segment to get member information about
     *
     * @param int $marker
     * A marker value returned by a previous GET call. Use this to grab the next batch of records.
     *
     * @return bool|mixed
     */
    public function getGroupMemberIdentifiers( $groupId, int $marker = null )
    {
        $params = $this->createRequestBody(
            $this->filterParams(
                array(
                    'marker' => $marker
                )
            )
        );

        $path = sprintf('%s/%s/%s/%s',self::GROUP, $groupId, self::MEMBERS, 'all' );

        return $this->v2Request( $path, $params );
    }
}