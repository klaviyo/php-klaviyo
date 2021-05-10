<?php

declare(strict_types=1);

namespace Klaviyo\Model;

use Klaviyo\Exception\KlaviyoException;
use JsonSerializable;

/**
 * Class defining a profile in Klaviyo with methods to set
 * various special properties.
 */
class ProfileModel implements JsonSerializable
{
    public string $id;
    public string $email;
    public string $firstName;
    public string $lastName;
    public string $phoneNumber;
    public string $title;
    public string $city;
    public string $organization;
    public string $region;
    public string $country;
    public string $zip;

    /**
     * Special attributes as identified by Klaviyo
     *
     * @var string[]
     */
    public static array $specialAttributes = [
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

    /**
     * Attributes of a profile used to identify each
     *
     * @var string[]
     */
    public static array $identifyAttributes = [
        '$email',
        '$id',
        '$phone_number',
        '$ios_tokens',
    ];

    protected array $customAttributes;

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
    public function __construct(array $configuration)
    {
        $profileIdentityValues = array_intersect_key($configuration, array_flip(self::$identifyAttributes));
        if (empty($profileIdentityValues)) {
            throw new KlaviyoException(
                sprintf(
                    'ProfileModel requires one of the following fields for identification: %s',
                    implode(', ', self::$identifyAttributes)
                )
            );
        }
        $this->setAttributes($configuration);
    }

    public function getCustomAttribute(string $attributeKey) : string
    {
        return !empty($this->customAttributes[$attributeKey]) ? $this->customAttributes[$attributeKey] : '';
    }

    public function getCustomAttributes() : array
    {
        return $this->customAttributes;
    }

    /**
     * The Special attributes array is using snake case, while class properties are camelCased.
     * This means that variables such as first_name or last_name won't exist on the class object and will
     * simply be missed inside jsonSerialize method. To accomodate for that, we convert all the keys to camelCase before running the comparison.
     *
     * @return string
     */
    public function convertToCamelCase($key) : string
    {
        return lcfirst(str_replace('_', '', ucwords(ltrim($key, '$'), '_')));
    }

    public function jsonSerialize() : array
    {
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

    public function toArray()
    {
        return json_decode(json_encode($this), true);
    }

    protected function setAttributes(array $configuration) : void
    {
        foreach ($configuration as $key => $value) {
            if ($this->isSpecialAttribute($key)) {
                $this->{$this->convertToCamelCase($key)} = $value;
            }
        }

        $this->setCustomAttributes($configuration);
    }

    protected function isSpecialAttribute(string $attributeKey) : bool
    {
        return in_array($attributeKey, self::$specialAttributes);
    }

    protected function isCustomAttribute(string $attributeKey) : bool
    {
        return !self::isSpecialAttribute($attributeKey);
    }

    private function setCustomAttributes(array $configuration) : void
    {
        $customAttributeKeys = array_flip(
            array_filter(
                array_keys($configuration),
                'self::isCustomAttribute'
            )
        );
        $customAttributes = array_intersect_key($configuration, $customAttributeKeys);
        $this->customAttributes = $customAttributes;
    }
}
