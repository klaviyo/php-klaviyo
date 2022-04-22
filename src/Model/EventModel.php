<?php

namespace Klaviyo\Model;

use DateTime;
use Exception;

use Klaviyo\Exception\KlaviyoException;
use Klaviyo\Model\ProfileModel;

class EventModel extends BaseModel
{
    protected $event;
    protected $customer_properties;
    protected $properties;
    protected $time;

    /**
     * EventModel constructor. Takes in a config array which needs to be set up as follows:
     * token: string (This is your public API key).
     *
     * event: string (Name of the event you want to track)
     *
     * customer_properties: hash/dictionary (Custom information about the person who did this event,
     * You must identify the person by their email, using a $email key, or a unique identifier, using a $id.
     * Other than that, you can include any data you want and it can then be used to create segments of people.
     * For example, if you wanted to create a list of people on trial plans, include a person's plan type in this hash so you can use that information later)
     *
     * properties: optional, hash/dictionary or null (Custom information about this event.
     * Any properties included here can be used for creating segments later
     * For example, if you track an event called "Posted Item," you could include a property for item type (e.g. image, article, etc.).)
     *
     * time: optional, UNIX timestamp or null (When this event occurred. By default, Klaviyo assumes events happen when a request is made.
     * If you'd like to track and event that happened in past, use this property.)
     *
     * @param array $config
     * @throws KlaviyoException
     */
    public function __construct( $config ) {
        $this->event = $config['event'];
        $this->customer_properties = new ProfileModel(
            $config['customer_properties']
        );
        $this->properties = empty($config['properties']) == false ? $config['properties'] : [];
        // Can pass in unix timestamp if prefixed with '@' or any date/time format accepted by DateTime interface.
        try {
            if (isset($config['time'])) {
                $time = new DateTime(
                    is_int($config['time']) ? '@' . $config['time'] : $config['time']
                );
            }
        } catch ( Exception $e ) {
            throw new KlaviyoException( $e->getMessage() );
        }

        $this->time = !empty($config['time']) ? $time->getTimestamp() : null;
    }

    /**
     * @return array|mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return array(
            'event' => $this->event,
            'customer_properties' => $this->customer_properties,
            'properties' => $this->properties,
            'time' => $this->time
        );
    }

}
