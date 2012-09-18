Klaviyo PHP SDK
============

Library include a class for using the Klaviyo API from PHP.

Example usage
-------------

    $tracker = new Klaviyo("YOUR_TOKEN");
    $tracker->track(
        'Purchased item',
        array('sku' => '123456', 'payment_method' => 'credit card'),
        array('$email' => 'someone@example.com', '$first_name' => 'Bill', '$last_name' => 'Smith')
    );