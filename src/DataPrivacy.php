<?php

namespace Klaviyo;

use Klaviyo\Exception\KlaviyoException;

/**
 * Class DataPrivacy
 * @package Klaviyo
 */
class DataPrivacy extends KlaviyoAPI
{
    /**
     * Data Privacy endpoint constants
     */
    const ENDPOINT_DATA_PRIVACY = 'data-privacy';
    const ENDPOINT_DELETION_REQUEST = 'deletion-request';

    /**
     * Data Privacy arguments
     */
    const PHONE_NUMBER = 'phone_number';
    const PERSON_ID = 'person_id';

    const PROFILE_DELETION_VALID_ID_TYPES = [
        self::EMAIL,
        self::PHONE_NUMBER,
        self::PERSON_ID,
    ];

    /**
     * Request a data privacy-compliant deletion for the person record corresponding
     * to an email address, phone number, or person identifier. If multiple person
     * records exist for the provided identifier, only one of them will be deleted.
     *
     * @link https://www.klaviyo.com/docs/api/v2/data-privacy#post-deletion-request
     *
     * @param $identifier string Value by which to identify the profile being deleted.
     * @param $idType string Identifier type e.g. email, phone_number, person_id.
     * @return mixed
     * @throws KlaviyoException
     */
    #[\ReturnTypeWillChange]
    public function requestProfileDeletion($identifier, $idType = self::EMAIL)
    {
        if (!in_array($idType, self::PROFILE_DELETION_VALID_ID_TYPES)) {
            throw new KlaviyoException(
                sprintf(
                    'Invalid id_type provided, must be one of: %s',
                    implode(', ', self::PROFILE_DELETION_VALID_ID_TYPES)
                )
            );
        }

        $options = $this->createParams($idType, $identifier);
        $path = sprintf('%s/%s', self::ENDPOINT_DATA_PRIVACY, self::ENDPOINT_DELETION_REQUEST);

        return $this->v2Request($path, $options, self::HTTP_POST);
    }
}
