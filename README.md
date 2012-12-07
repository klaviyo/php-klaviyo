Klaviyo PHP SDK
============

Library include a class for using the Klaviyo API from PHP.

Example usage
-------------

    $tracker = new Klaviyo("YOUR_TOKEN");
    $tracker->track(
        'Purchased item',
        array('$email' => 'someone@example.com', '$first_name' => 'Bill', '$last_name' => 'Shakespeare'),
        array('Item SKU' => 'ABC123', 'Payment Method' => 'Credit Card'),
        1354913220
    );

Argument descrption
-------------

The `track` method takes four arguments:

**event** This is the name of the event you want to track. It can be any string you like.

**customer_properties** This is an associative array of properties that belong to the person who did the action you're recording. You must include either a `$id` or `$email` key.

**properties** (optional) This is an associative array of properties that are specific to this event occurrance. In the above example, we included the SKU of the item that was purchased and the payment method.

**timestamp** (optional) This is the timestamp when this event occurred. You only need to include this if you're tracking events that occurred in the past. If you're tracking activity in real time, you can ignore this argument.