<?php

namespace Klaviyo\Model;

use Klaviyo\Exception\KlaviyoException;

/**
 * Class defining a profile in Klaviyo with methods to set
 * various special properties.
 */
class ProfileModel extends BaseModel
{
    public $id;
    public $email;
    public $firstName;
    public $lastName;
    public $phoneNumber;
    public $title;
    public $city;
    public $organization;
    public $region;
    public $country;
    public $zip;
    protected $customAttributes;

    public static $specialAttributes = [
        '$id',
        '$email',
        '$first_name',
        '$last_name',
        '$organization',
        '$title',
        '$address1',
        '$address2',
        '$city',
        '$region',
        '$zip',
        '$country',
        '$timezone',
        '$phone_number',
        '$ios_tokens',
    ];

    public static $identifyAttributes = [
        '$email',
        '$id',
        '$phone_number',
        '$ios_tokens',
    ];

    public function __construct( array $configuration ) {
        if (empty(array_intersect_key( $configuration, array_flip(self::$identifyAttributes) ))) {
            throw new KlaviyoException(
                sprintf(
                    'ProfileModel requires one of the following fields for identification: %s',
                    implode(', ', self::$identifyAttributes)
                )
            );
        }
        $this->setAttributes( $configuration );
    }

    protected function setAttributes( array $configuration ) {
        foreach ( $configuration as $key => $value ) {
            if ( $this->isSpecialAttribute($key) ) {
                $this->{ltrim($key, '$')} = $value;
            }
            
        }

        $this->setCustomAttributes( $configuration );
    }

    private function setCustomAttributes( array $configuration ) {
        $customAttributeKeys = array_flip(
            array_filter(
                array_keys( $configuration ),
                'self::isCustomAttribute'
            )
        );
        $customAttributes = array_intersect_key( $configuration, $customAttributeKeys );
        $this->customAttributes = $customAttributes;
    }

    protected function isSpecialAttribute( $attributeKey ) {
        return in_array( $attributeKey, self::$specialAttributes );
    }

    protected function isCustomAttribute( $attributeKey ) {
        return !self::isSpecialAttribute( $attributeKey );
    }

    public function getCustomAttribute( $attributeKey ) {
        return !empty($this->customAttributes[$attributeKey]) ? $this->customAttributes[$attributeKey] : '';
    }

    public function getCustomAttributes() {
        return $this->customAttributes;
    }

    public function jsonSerialize() {
        return [
            'email' => $this->email,
            '$phone_number' => $this->phoneNumber,
            '$first_name' => $this->firstName,
            '$last_name' => $this->lastName
        ] + $this->getCustomAttributes();
    }
}