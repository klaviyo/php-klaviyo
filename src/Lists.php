<?php

namespace Klaviyo;

use Klaviyo\Model\ProfileModel;

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

    /**
     * Create list
     *
     * @param string $listName Name of list to be created.
     *
     * @return Guzzle response object
     */
    public function createList( $listName ) {
        $options = $this->createOptions('list_name', $listName);

        return $this->v2Request( self::LISTS, $options, self::HTTP_POST );
    }

    /**
     * Get all lists
     *
     * @return Guzzle response object
     */
    public function getLists() {

        return $this->v2Request( self::LISTS );
    }

    /**
     * Add members to a list. Validate incoming array of profiles all conform to
     * ProfileModel.
     *
     * @param string $listId List ID to which profiles will be added.
     * @param array $profiles Profiles to be added to the specified list.
     * @return Guzzle response object
     */
    public function addMembersToList( $listId, array $profiles ) {
        array_walk($profiles, [$this, 'isInstanceOf'], __NAMESPACE__ . '\Model\ProfileModel');
        $profiles = array_map(
            function( $profile ) {
                return $profile->toArray();
            }, $profiles
        );

        $path = sprintf( '%s/%s/%s', self::LIST, $listId, self::MEMBERS );
        $options = $this->createOptions( 'profiles', $profiles );
        var_dump($options);

        return $this->v2Request( $path, $options, self::HTTP_POST );
    }
}