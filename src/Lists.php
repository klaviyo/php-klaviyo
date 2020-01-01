<?php

namespace Klaviyo;

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
     * Get all lists
     *
     * @return Guzzle response object
     */
    public function getLists() {

        return $this->v2Request( self::LISTS );
    }

    /**
     * Add members to a list
     *
     * @param string $listId List ID to which profiles will be added.
     * @param array $profiles Profiles to be added to the specified list.
     * @return Guzzle response object
     */
    public function addMembersToList( $listId, array $profiles ) {
        // TODO: Validate incoming profiles? Profile interface and check each?
        $path = sprintf( '%s/%s/%s', self::LIST, $listId, self::MEMBERS );

        $options = [self::JSON =>
            ['profiles' => $profiles]
        ];

        print_r($options);

        return $this->v2Request( $path, $options, self::POST );
    }
}