<?php

declare(strict_types=1);

namespace Klaviyo;

/**
 * Class Lists
 * @package Klaviyo
 */
class Lists
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

    private KlaviyoAPI $klaviyoAPI;

    public function __construct(KlaviyoAPI $klaviyoAPI)
    {
        $this->klaviyoAPI = $klaviyoAPI;
    }

    /**
     * Create a new list
     * @link https://www.klaviyo.com/docs/api/v2/lists#post-lists
     *
     * @param string $listName Name of list to be created.
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function createList(string $listName) : array
    {
        $options = $this->klaviyoAPI->createParams(self::LIST_NAME, $listName);

        return $this->klaviyoAPI->v2Request(self::ENDPOINT_LISTS, $options, KlaviyoAPI::HTTP_POST);
    }

    /**
     * Get all lists
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-lists
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function getLists() : array
    {
        return $this->klaviyoAPI->v2Request(self::ENDPOINT_LISTS);
    }

    /**
     * Get information about a list
     *
     * @param string $listId 6 digit unique identifier of the list
     *
     * @return array
     * @throws Exception\KlaviyoException
     *
     * @see getListById
     *
     * @deprecated 2.2.6
     */
    public function getListDetails(string $listId) : array
    {
        return $this->getListById($listId);
    }

    /**
     * Get information about a list.
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-list
     *
     * @param string $listId 6 digit unique identifier of the list
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function getListById(string $listId) : array
    {
        $path = sprintf('%s/%s', self::ENDPOINT_LIST, $listId);
        return $this->klaviyoAPI->v2Request($path);
    }

    /**
     * Update a list's properties
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param string $list_name to update list name to
     *
     * @return array
     * @throws Exception\KlaviyoException
     *
     * @see updateListNameById
     *
     * @deprecated 2.2.6
     */
    public function updateListDetails(string $listId, string $list_name) : array
    {
        return $this->updateListNameById($listId, $list_name);
    }

    /**
     * Update a list's name.
     * @link https://www.klaviyo.com/docs/api/v2/lists#put-list
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param string $listName String to update list name to
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function updateListNameById(string $listId, string $listName) : array
    {
        $params = $this->klaviyoAPI->createRequestBody(
            [self::LIST_NAME => $listName]
        );

        $path = sprintf('%s/%s', self::ENDPOINT_LIST, $listId);

        return $this->klaviyoAPI->v2Request($path, $params, KlaviyoAPI::HTTP_PUT);
    }

    /**
     * Delete a list from an account. This is a destructive operation and cannot be undone. It will also remove flow triggers associated with the list.
     * @link https://www.klaviyo.com/docs/api/v2/lists#delete-list
     *
     * @param string $listId 6 digit unique identifier of the list
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function deleteList(string $listId) : array
    {
        $path = sprintf('%s/%s', self::ENDPOINT_LIST, $listId);
        return $this->klaviyoAPI->v2Request($path, [], KlaviyoAPI::HTTP_DELETE);
    }

    /**
     * Subscribe or re-subscribe profiles to a list. Profiles will be single or double opted into the specified list in accordance with that list’s settings.
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param array $profiles
     * The profiles that you would like to subscribe. Each object in the list must have either an email or phone number key.
     * You can also provide additional properties as key-value pairs. If you are a GDPR compliant business, you will need to include $consent in your API call.
     * $consent is a Klaviyo special property and only accepts the following values: "email", "web", "sms", "directmail", "mobile".
     * If you are updating consent for a phone number or would like to send an opt-in SMS to the profile (for double opt-in lists), include an sms_consent key in the profile with a value of true or false.
     *
     * @return array
     * @throws Exception\KlaviyoException
     *
     * @see addSubscribersToList
     *
     * @deprecated 2.2.6
     */
    public function subscribeMembersToList(string $listId, array $profiles) : array
    {
        return $this->addSubscribersToList($listId, $profiles);
    }

    /**
     * Subscribe or re-subscribe profiles to a list. Profiles will be single or double opted into the specified list in accordance with that list’s settings.
     * @link https://www.klaviyo.com/docs/api/v2/lists#post-subscribe
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param array $profiles
     * The profiles that you would like to subscribe. Each object in the list must have either an email or phone number key.
     * You can also provide additional properties as key-value pairs. If you are a GDPR compliant business, you will need to include $consent in your API call.
     * $consent is a Klaviyo special property and only accepts the following values: "email", "web", "sms", "directmail", "mobile".
     * If you are updating consent for a phone number or would like to send an opt-in SMS to the profile (for double opt-in lists), include an sms_consent key in the profile with a value of true or false.
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function addSubscribersToList(string $listId, array $profiles) : array
    {
        $this->klaviyoAPI->checkProfile($profiles);

        $profiles = array_map(
            function ($profile) {
                return $profile->toArray();
            },
            $profiles
        );

        $path = sprintf('%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_SUBSCRIBE);
        $params = $this->klaviyoAPI->createParams(KlaviyoAPI::PROFILES, $profiles);

        return $this->klaviyoAPI->v2Request($path, $params, KlaviyoAPI::HTTP_POST);
    }

    /**
     * Check if profiles are on a list and not suppressed.
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-subscribe
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param array|null $emails The emails corresponding to the profiles that you would like to check.
     * @param array|null $phoneNumbers The phone numbers corresponding to the profiles that you would like to check.
     * Phone numbers must be in E.164 format.
     * @param array|null $pushTokens The push tokens corresponding to the profiles that you would like to check.
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function checkListSubscriptions($listId, ?array $emails = null, ?array $phoneNumbers = null, ?array $pushTokens = null) : array
    {
        $params = $this->klaviyoAPI->createRequestJson(
            $this->klaviyoAPI->filterParams(
                [
                    self::EMAILS => $emails,
                    self::PHONE_NUMBERS => $phoneNumbers,
                    self::PUSH_TOKENS => $pushTokens,
                ]
            )
        );

        $path = sprintf('%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_SUBSCRIBE);

        return $this->klaviyoAPI->v2Request($path, $params, KlaviyoAPI::HTTP_GET);
    }

    /**
     * Unsubscribe and remove profiles from a list.
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param array $emails The emails corresponding to the profiles that you would like to check.
     *
     * @return array
     * @throws Exception\KlaviyoException
     *
     * @see deleteSubscribersFromList
     *
     * @deprecated 2.2.6
     */
    public function unsubscribeMembersFromList(string $listId, array $emails) : array
    {
        return $this->deleteSubscribersFromList($listId, $emails);
    }

    /**
     * Unsubscribe and remove profiles from a list.
     * @link https://www.klaviyo.com/docs/api/v2/lists#delete-subscribe
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param array $emails The emails corresponding to the profiles that you would like to check.
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function deleteSubscribersFromList(string $listId, array $emails) : array
    {
        $params = $this->klaviyoAPI->createRequestJson(
            $this->klaviyoAPI->filterParams(
                [
                    self::EMAILS => $emails,
                ]
            )
        );

        $path = sprintf('%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_SUBSCRIBE);

        return $this->klaviyoAPI->v2Request($path, $params, KlaviyoAPI::HTTP_DELETE);
    }

    /**
     * Use this endpoint to add profiles to and remove profiles from Klaviyo lists without changing their subscription or suppression status.
     * @link https://www.klaviyo.com/docs/api/v2/lists#post-members
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param array $profiles
     * The profiles that you would like to subscribe. Each object in the list must have either an email or phone number key.
     * You can also provide additional properties as key-value pairs. If you are a GDPR compliant business, you will need to include $consent in your API call.
     * $consent is a Klaviyo special property and only accepts the following values: "email", "web", "sms", "directmail", "mobile".
     * If you are updating consent for a phone number or would like to send an opt-in SMS to the profile (for double opt-in lists), include an sms_consent key in the profile with a value of true or false.
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function addMembersToList(string $listId, array $profiles) : array
    {
        $this->klaviyoAPI->checkProfile($profiles);

        $profiles = array_map(
            function ($profile) {
                return $profile->toArray();
            },
            $profiles
        );

        $path = sprintf('%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_MEMBERS);
        $options = $this->klaviyoAPI->createParams(KlaviyoAPI::PROFILES, $profiles);

        return $this->klaviyoAPI->v2Request($path, $options, KlaviyoAPI::HTTP_POST);
    }

    /**
     * Check if profiles are on a list.
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-members
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param array|null $emails The emails corresponding to the profiles that you would like to check.
     * @param array|null $phoneNumbers The phone numbers corresponding to the profiles that you would like to check.
     * Phone numbers must be in E.164 format.
     * @param array|null $pushTokens The push tokens corresponding to the profiles that you would like to check.
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function checkListMembership(string $listId, ?array $emails = null, ?array $phoneNumbers = null, ?array $pushTokens = null) : array
    {
        $params = $this->klaviyoAPI->createRequestJson(
            $this->klaviyoAPI->filterParams(
                [
                    self::EMAILS => $emails,
                    self::PHONE_NUMBERS => $phoneNumbers,
                    self::PUSH_TOKENS => $pushTokens,
                ]
            )
        );

        $path = sprintf('%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_MEMBERS);

        return $this->klaviyoAPI->v2Request($path, $params, KlaviyoAPI::HTTP_GET);
    }

    /**
     * Remove profiles from a list.
     * @link https://www.klaviyo.com/docs/api/v2/lists#delete-members
     *
     * @param string $listId 6 digit unique identifier of the list.
     * @param array $emails The emails corresponding to the profiles that you would like to check.
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function removeMembersFromList(string $listId, array $emails) : array
    {
        $params = $this->klaviyoAPI->createRequestJson(
            $this->klaviyoAPI->filterParams(
                [
                    self::EMAILS => $emails,
                ]
            )
        );

        $path = sprintf('%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_MEMBERS);

        return $this->klaviyoAPI->v2Request($path, $params, KlaviyoAPI::HTTP_DELETE);
    }

    /**
     * Get all of the emails and phone numbers that have been excluded from a list along with the exclusion reasons and exclusion time.
     * This endpoint uses batching to return the records, so for a large list multiple calls will need to be made to get all of the records.
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param int|null $marker A marker value returned by a previous GET call. Use this to grab the next batch of records.
     *
     * @return array
     * @see getListExclusions
     *
     * @deprecated 2.2.6
     */
    public function getAllExclusionsOnList(string $listId, ?int $marker = null) : array
    {
        return $this->getListExclusions($listId, $marker);
    }

    /**
     * Get all of the emails and phone numbers that have been excluded from a list along with the exclusion reasons and exclusion time.
     * This endpoint uses batching to return the records, so for a large list multiple calls will need to be made to get all of the records.
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-exclusions-all
     *
     * @param string $listId 6 digit unique identifier of the list
     * @param int|null $marker A marker value returned by a previous GET call. Use this to grab the next batch of records.
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function getListExclusions(string $listId, ?int $marker = null) : array
    {
        $params = $this->klaviyoAPI->createRequestBody(
            $this->klaviyoAPI->filterParams(
                [
                    self::MARKER => $marker,
                ]
            )
        );

        $path = sprintf('%s/%s/%s/%s', self::ENDPOINT_LIST, $listId, self::ENDPOINT_EXCLUSIONS, self::ENDPOINT_ALL);

        return $this->klaviyoAPI->v2Request($path, $params);
    }

    /**
     * Get all of the emails, phone numbers, and push tokens for profiles in a given list or segment.
     * This endpoint uses batching to return the records, so for a large list or segment multiple calls will need to be made to get all of the records.
     *
     * @param string $groupId 6 digit unique identifier of List/Segment to get member information about
     * @param int|null $marker A marker value returned by a previous GET call. Use this to grab the next batch of records.
     *
     * @return array
     * @throws Exception\KlaviyoException
     *
     * @see getAllMembers
     *
     * @deprecated 2.2.6
     */
    public function getGroupMemberIdentifiers(string $groupId, ?int $marker = null) : array
    {
        return $this->getAllMembers($groupId, $marker);
    }

    /**
     * Get all of the emails, phone numbers, and push tokens for profiles in a given list or segment.
     * This endpoint uses batching to return the records, so for a large list or segment multiple calls will need to be made to get all of the records.
     * @link https://www.klaviyo.com/docs/api/v2/lists#get-members-all
     *
     * @param string $groupId 6 digit unique identifier of List/Segment to get member information about
     * @param int|null $marker A marker value returned by a previous GET call. Use this to grab the next batch of records.
     *
     * @return array
     * @throws Exception\KlaviyoException
     */
    public function getAllMembers(string $groupId, ?int $marker = null) : array
    {
        $params = $this->klaviyoAPI->createRequestBody(
            $this->klaviyoAPI->filterParams(
                [
                    self::MARKER => $marker,
                ]
            )
        );

        $path = sprintf('%s/%s/%s/%s', self::ENDPOINT_GROUP, $groupId, self::ENDPOINT_MEMBERS, self::ENDPOINT_ALL);
        return $this->klaviyoAPI->v2Request($path, $params);
    }
}
