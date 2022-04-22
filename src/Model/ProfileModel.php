<?php

namespace Klaviyo\Model;

use Klaviyo\Exception\KlaviyoException;

/**
 * Class defining a profile in Klaviyo with methods to set
 * various special properties.
 */
class ProfileModel extends BaseModel
{
    /**
     * @var
     */
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

    /**
     * @var
     */
    protected $customAttributes;

    /**
     * Special attributes as identified by Klaviyo
     *
     * @var string[]
     */
    public static $specialAttributes = array(
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
    );

    /**
     * Attributes of a profile used to identify each
     *
     * @var string[]
     */
    public static $identifyAttributes = array(
        '$email',
        '$id',
        '$phone_number',
        '$ios_tokens',
    );

    /**
     * ProfileModel constructor.
     * Takes an array as input to construct Profiles, requires email, id, phoneNumber or pushToken
     *
     * properties: hash/dictionary
     * Custom information about the person who did this event.
     * You must identify the person by their email, using a $email key, or a unique identifier, using a $id.
     * Other than that, you can include any data you want and it can then be used to create segments of people.
     * For example, if you wanted to create a list of people on trial plans, include a person's plan type in this hash so you can use that information later.
     *
     * @param array $configuration
     * @throws KlaviyoException
     */
    public function __construct( array $configuration ) {
        $profileIdentityValues = array_intersect_key( $configuration, array_flip(self::$identifyAttributes) );
        if (empty($profileIdentityValues)) {
            throw new KlaviyoException(
                sprintf(
                    'ProfileModel requires one of the following fields for identification: %s',
                    implode(', ', self::$identifyAttributes)
                )
            );
        }
        $this->setAttributes( $configuration );
    }

    /**
     * @param array $configuration
     */
    #[\ReturnTypeWillChange]
    protected function setAttributes(array $configuration ) {
        foreach ( $configuration as $key => $value ) {
            if ( $this->isSpecialAttribute($key) ) {
                $this->{$this->convertToCamelCase($key)} = $value;
            }

        }

        $this->setCustomAttributes( $configuration );
    }

    /**
     * @param array $configuration
     */
    #[\ReturnTypeWillChange]
    private function setCustomAttributes(array $configuration ) {
        $customAttributeKeys = array_flip(
            array_filter(
                array_keys( $configuration ),
                'self::isCustomAttribute'
            )
        );
        $customAttributes = array_intersect_key( $configuration, $customAttributeKeys );
        $this->customAttributes = $customAttributes;
    }

    /**
     * @param $attributeKey
     * @return bool
     */
    #[\ReturnTypeWillChange]
    protected function isSpecialAttribute($attributeKey ) {
        return in_array( $attributeKey, self::$specialAttributes );
    }

    /**
     * @param $attributeKey
     * @return bool
     */
    #[\ReturnTypeWillChange]
    protected function isCustomAttribute($attributeKey ) {
        return !self::isSpecialAttribute( $attributeKey );
    }

    /**
     * @param $attributeKey
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function getCustomAttribute($attributeKey ) {
        return !empty($this->customAttributes[$attributeKey]) ? $this->customAttributes[$attributeKey] : '';
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function getCustomAttributes() {
        return $this->customAttributes;
    }

    /**
     * The Special attributes array is using snake case, while class properties are camelCased.
     * This means that variables such as first_name or last_name won't exist on the class object and will
     * simply be missed inside jsonSerialize method. To accomodate for that, we convert all the keys to camelCase before running the comparison.
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function convertToCamelCase($key) {
        return lcfirst(str_replace('_', '', ucwords(ltrim($key, '$'), '_')));
    }
    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        $properties = array_fill_keys($this::$specialAttributes, null);
        foreach ($properties as $key => &$value) {
            if (!property_exists($this, $this->convertToCamelCase($key))) {
                continue;
            }

            $value = $this->{$this->convertToCamelCase($key)};
        }

        unset($properties['$email']);
        $properties['email'] = $this->email;

        return array_merge(
            $properties,
            $this->getCustomAttributes()
        );
    }
}
