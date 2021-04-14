<?php

namespace Klaviyo;

/**
 * Class Lists
 * @package Klaviyo
 */
class Lists extends KlaviyoAPI
{
    /**
     * List endpoint constants
     */
    const ENDPOINT_ALL = 'all';
    const ENDPOINT_EXCLUSIONS = 'exclusions';
    const ENDPOINT_GROUP = 'group';
    const ENDPOINT_LIST = 'list';
    const ENDPOINT_LISTS = 'lists';
    const ENDPOINT_MEMBERS = 'members';
    const ENDPOINT_SEGMENT = 'segment';
    const ENDPOINT_SUBSCRIBE = 'subscribe';

    /**
     * Lists API arguments
     */
    const EMAILS = 'emails';
    const PHONE_NUMBERS = 'phone_numbers';
    const PUSH_TOKENS = 'push_tokens';
    const LIST_NAME = 'list_name';
    const MARKER = 'marker';

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
        $options = $this->createParams(self::LIST_NAME, $listName);

        return $this->v2Request( self::ENDPOINT_LISTS, $options, self::HTTP_POST );
    }

    /**
     * Get all lists
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-lists
     *
     * @return bool/mixed
     */
    public function getLists() {

        return $this->v2Request( self::ENDPOINT_LISTS );
    }

    /**
     * Get information about a list
     *
     * @deprecated 2.2.6
     * @see getListById
     *
     * @param string $listId
     * 6 digit unique identifier of the list
     *
     * @return bool|mixed
     */
    public function getListDetails( $listId )
    {
        return $this->getListById($listId);
    }

    /**
     * Get information about a list.
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-list
     *
     * @param string $listId
     * 6 digit unique identifier of the list
     *
     * @return bool|mixed
     */
    public function getListById($listId)
    {
        $path = sprintf('%s/%s', self::ENDPOINT_LIST, $listId);
        return $this->v2Request($path);
    }

    /**
     * Update a list's properties
     *
     * @deprecated 2.2.6
     * @see updateListNameById
     *
     * @param $listId
     * 6 digit unique identifier of the list
     *
     * @param $list_name
     * String to update list name to
     *
     * @return bool|mixed
     */
    public function updateListDetails( $listId, $list_name )
    {
        return $this->updateListNameById($listId, $list_name);
    }

    /**
     * Update a list's name.
     * @link https://www.klaviyo.com/docs/api/v2/lists#put-list
     *
     * @param string $listId
     * 6 digit unique identifier of the list
     *
     * @param string $listName
     * String to update list name to
     *
     * @return bool|mixed
     */
    public function updateListNameById($listId, $listName)
    {
        $params = $this->createRequestBody(
            array(self::LIST_NAME => $listName)
        );

        $path = sprintf('%s/%s', self::ENDPOINT_LIST, $listId);

        return $this->v2Request($path, $params, self::HTTP_PUT);
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
    public function deleteList( $listId )
    {
        $path = sprintf( '%s/%s', self::ENDPOINT_LIST, $listId );
        return $this->v2Request( $path, [], self::HTTP_DELETE );
    }

    /**
     * Subscribe or re-subscribe profiles to a list. Profiles will be single or double opted into the specified list in accordance with that list’s settings.
     *
     * @deprecated 2.2.6
     * @see addSubscribersToList
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
     * @throws Exception\KlaviyoException
     */
    public function subscribeMembersToList( $listId, $profiles )
    {
        return $this->addSubscribersToList($listId, $profiles);
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
     * @throws Exception\KlaviyoException
     */
    public function addSubscribersToList($listId, $profiles)
    {
        $this->checkProfile($profiles);

        $profiles = array_map(
            function($profile) {
                return $profile->toArray();
            },
            $profiles
        );

        $path = sprintf('%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_SUBSCRIBE);
        $params = $this->createParams(self::PROFILES, $profiles);

        return $this->v2Request($path, $params, self::HTTP_POST);
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
     * The push tokens corresponding to the profiles that you would like to check.
     *
     * @return bool|mixed
     */
    public function checkListSubscriptions ($listId, $emails = null,  $phoneNumbers = null, $pushTokens = null )
    {
        $params = $this->createRequestJson(
            $this->filterParams(
                array(
                    self::EMAILS => $emails,
                    self::PHONE_NUMBERS => $phoneNumbers,
                    self::PUSH_TOKENS => $pushTokens
                )
            )
        );

        $path = sprintf('%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_SUBSCRIBE );

        return $this->v2Request( $path, $params, self::HTTP_GET );
    }

    /**
     * Unsubscribe and remove profiles from a list.
     *
     * @deprecated 2.2.6
     * @see deleteSubscribersFromList
     *
     * @param string $listId
     * 6 digit unique identifier of the list
     *
     * @param array $emails
     * The emails corresponding to the profiles that you would like to check.
     *
     * @return bool|mixed
     */
    public function unsubscribeMembersFromList( $listId, $emails )
    {
        return $this->deleteSubscribersFromList($listId, $emails);
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
    public function deleteSubscribersFromList($listId, $emails)
    {
        $params = $this->createRequestJson(
            $this->filterParams(
                array(
                    self::EMAILS => $emails
                )
            )
        );

        $path = sprintf('%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_SUBSCRIBE);

        return $this->v2Request($path, $params, self::HTTP_DELETE);
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
     * @throws Exception\KlaviyoException
     */
    public function addMembersToList( $listId, $profiles )
    {
        $this->checkProfile( $profiles );

        $profiles = array_map(
            function( $profile ) {
                return $profile->toArray();
            }, $profiles
        );

        $path = sprintf( '%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_MEMBERS );
        $options = $this->createParams( self::PROFILES, $profiles );

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
    public function checkListMembership( $listId,  $emails = null, $phoneNumbers = null, $pushTokens = null )
    {
        $params = $this->createRequestJson(
            $this->filterParams(
                array(
                    self::EMAILS => $emails,
                    self::PHONE_NUMBERS => $phoneNumbers,
                    self::PUSH_TOKENS => $pushTokens
                )
            )
        );

        $path = sprintf( '%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_MEMBERS );

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
    public function removeMembersFromList( $listId, $emails )
    {
        $params = $this->createRequestJson(
            $this->filterParams(
                array(
                    self::EMAILS => $emails
                )
            )
        );

        $path = sprintf('%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_MEMBERS );

        return $this->v2Request( $path, $params, self::HTTP_DELETE );
    }

    /**
     * Get all of the emails and phone numbers that have been excluded from a list along with the exclusion reasons and exclusion time.
     * This endpoint uses batching to return the records, so for a large list multiple calls will need to be made to get all of the records.
     *
     * @deprecated 2.2.6
     * @see getListExclusions
     *
     * @param $listId
     * 6 digit unique identifier of the list
     *
     * @param int $marker
     * A marker value returned by a previous GET call. Use this to grab the next batch of records.
     *
     * @return bool|mixed
     */
    public function getAllExclusionsOnList( $listId, $marker = null )
    {
        return $this->getListExclusions($listId, $marker);
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
    public function getListExclusions($listId, $marker = null)
    {
        $params = $this->createRequestBody(
            $this->filterParams(
                array(
                    self::MARKER => $marker
                )
            )
        );

        $path = sprintf('%s/%s/%s/%s',self::ENDPOINT_LIST, $listId, self::ENDPOINT_EXCLUSIONS, self::ENDPOINT_ALL);

        return $this->v2Request($path, $params);
    }

    /**
     * Get all of the emails, phone numbers, and push tokens for profiles in a given list or segment.
     * This endpoint uses batching to return the records, so for a large list or segment multiple calls will need to be made to get all of the records.
     *
     * @deprecated 2.2.6
     * @see getAllMembers
     *
     * @param $groupId
     * 6 digit unique identifier of List/Segment to get member information about
     *
     * @param int $marker
     * A marker value returned by a previous GET call. Use this to grab the next batch of records.
     *
     * @return bool|mixed
     */
    public function getGroupMemberIdentifiers( $groupId, $marker = null )
    {
        return $this->getAllMembers($groupId, $marker);
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
    public function getAllMembers($groupId, $marker = null)
    {
        $params = $this->createRequestBody(
            $this->filterParams(
                array(
                    self::MARKER => $marker
                )
            )
        );

        $path = sprintf('%s/%s/%s/%s',self::ENDPOINT_GROUP, $groupId, self::ENDPOINT_MEMBERS, self::ENDPOINT_ALL);
        return $this->v2Request($path, $params);
    }
}
