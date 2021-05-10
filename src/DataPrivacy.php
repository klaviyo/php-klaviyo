<?php

declare(strict_types=1);

namespace Klaviyo;

use Klaviyo\Exception\KlaviyoException;

class DataPrivacy
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
        KlaviyoAPI::EMAIL,
        self::PHONE_NUMBER,
        self::PERSON_ID,
    ];

    private KlaviyoAPI $klaviyoAPI;

    public function __construct(KlaviyoAPI $klaviyoAPI)
    {
        $this->klaviyoAPI = $klaviyoAPI;
    }

    /**
     * Request a data privacy-compliant deletion for the person record corresponding
     * to an email address, phone number, or person identifier. If multiple person
     * records exist for the provided identifier, only one of them will be deleted.
     *
     * @link https://www.klaviyo.com/docs/api/v2/data-privacy#post-deletion-request
     *
     * @param string $identifier Value by which to identify the profile being deleted.
     * @param string $idType Identifier type e.g. email, phone_number, person_id.
     * @return array
     * @throws KlaviyoException
     */
    public function requestProfileDeletion(string $identifier, string $idType = KlaviyoAPI::EMAIL) : array
    {
        if (!in_array($idType, self::PROFILE_DELETION_VALID_ID_TYPES)) {
            throw new KlaviyoException(
                sprintf(
                    'Invalid id_type provided, must be one of: %s',
                    implode(', ', self::PROFILE_DELETION_VALID_ID_TYPES)
                )
            );
        }

        $options = $this->klaviyoAPI->createParams($idType, $identifier);
        $path = sprintf('%s/%s', self::ENDPOINT_DATA_PRIVACY, self::ENDPOINT_DELETION_REQUEST);

        return $this->klaviyoAPI->v2Request($path, $options, KlaviyoAPI::HTTP_POST);
    }
}
