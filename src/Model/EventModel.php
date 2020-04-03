<?php

namespace Klaviyo\Model;

use Klaviyo\Exception\KlaviyoException;
use Klaviyo\Model\ProfileModel;

class EventModel extends BaseModel
{
    protected $event;
    protected $customer_properties;
    protected $properties;
    protected $time;
    
    public function __construct( array $config ) {
        $this->event = $config['event'];
        $this->customer_properties = new ProfileModel(
            $config['customer_properties']
        );
        $this->properties = $config['properties'];
        // Can pass in unix timestamp if prefixed with '@'. Else just let it parse the date
        $this->time = !empty($config['time']) ?
            new DateTime(
                is_int($config['time']) ? '@' . $config['time'] : $config['time']
            ) : null;
    }

    public function jsonSerialize() {
        return [
            'event' => $this->event,
            'customer_properties' => $this->customer_properties,
            'properties' => $this->properties,
            'time' => $this->time
        ];
    }
    
}