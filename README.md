# php-klaviyo - RETIRED

## Deprecation Notice

This SDK and its associated [composer package](https://packagist.org/packages/klaviyo/php-sdk) is set to be deprecated on 2023-04-01 and will not receive further updates. To continue receiving API and SDK improvements, please follow the instructions below to migrate to the new Klaviyo PHP SDK, which you can find on [Github](https://github.com/klaviyo/klaviyo-php-sdk) or [Packagist](https://packagist.org/packages/klaviyo/sdk).

## Migration Instructions

NOTE: this change is not backwards compatible; migrating to the new SDK requires completing the following steps:

### Install New SDK

`composer require klaviyo/sdk`

### Update Import 

From:
```php
use Klaviyo\Klaviyo as Klaviyo;
```

To:
 ```php
use Klaviyo\Client;
 ```

### Update Client Instantiation

From:
```php
$client = new Klaviyo( 'PRIVATE_API_KEY', 'PUBLIC_API_KEY' );
```

To:
```php
$client = new Client('YOUR_API_KEY');
```

### Updating SDK Calls

The new SDK has many changes to namespace (resource and function names), parameters (names, types, and format), and error handling. Additionally, the new SDK sticks to just standard/built-in PHP types (int, string, array, etc), and does not make use of custom Models, as this legacy one does. Please reference [this section](https://github.com/klaviyo/klaviyo-php-sdk#comprehensive-list-of-operations--parameters) of the new SDK repo for details on how to update each operation.

### Multistore limitation

The new SDK currently sets API keys at a global environment level. This means that if you manage multiple stores, each store's client must be running in a different environment. We plan to update this behavior to better support multistore applications.


# What is Klaviyo?

Klaviyo is a real-time service for understanding your customers by aggregating all your customer data, identifying important groups of customers and then taking action.
http://www.klaviyo.com/

## What does this package do?

* Track customers and events directly from your backend.


## How to install?

    composer require klaviyo/php-sdk

## API Examples

After installing the Klaviyo package you can initiate it using your public token which is for track events or identifying profiles and/or your private api key to utilize the metrics and list apis.
```php
use Klaviyo\Klaviyo as Klaviyo;

$client = new Klaviyo( 'PRIVATE_API_KEY', 'PUBLIC_API_KEY' );
```
You can then easily use Klaviyo to track events or identify people.  Note, track and identify requests take your public token.


### Track an event
```php
use Klaviyo\Model\EventModel as KlaviyoEvent;

$event = new KlaviyoEvent(
    array(
        'event' => 'Filled out Profile',
        'customer_properties' => array(
            '$email' => 'someone@mailinator.com'
        ),
        'properties' => array(
            'Added Social Accounts' => False
        )
    )
);

$client->publicAPI->track( $event, true );
```
### You can also add profile properties to the 'customer properties' attribute in the Event model
```php
use Klaviyo\Model\EventModel as KlaviyoEvent;

$event = new KlaviyoEvent(
    array(
        'event' => 'Filled out Profile',
        'customer_properties' => array(
            '$email' => 'someone@mailinator.com',
            '$first_name' => 'Thomas',
            '$last_name' => 'Jefferson'
        ),
        'properties' => array(
            'Added Social Accounts' => False
        )
    )
);

$client->publicAPI->track( $event, true );
```

### or just add a property to someone
```php
use Klaviyo\Model\ProfileModel as KlaviyoProfile;

$profile = new KlaviyoProfile(
    array(
        '$email' => 'thomas.jefferson@mailinator.com',
        '$first_name' => 'Thomas',
        '$last_name' => 'Jefferson',
        'Plan' => 'Premium'
    )
);

$client->publicAPI->identify( $profile, true );
```

### You can get metrics, a timeline of events and export analytics for a metric. See here for more https://www.klaviyo.com/docs/api/metrics

```php
#return a list of all metrics in your Klaviyo account
$client->metrics->getMetrics();

#return a timeline of all metrics
$client->metrics->getMetricsTimeline();

#return a specific metric timeline using its metric ID
$client->metrics->getMetricTimelineById( 'METRICID' );

#export metric specific values
$client->metrics->getMetricExport( 'METRICID' );
```

### You can create, update, read, and delete lists.  See here for more information https://www.klaviyo.com/docs/api/v2/lists
```php
#create a list
$client->lists->createList( 'List Name' );

#Get all lists in your Klaviyo account
$client->lists->getLists();

#Get information about a list
$client->lists->getListById( 'ListId' );

#update a lists properties
$client->lists->updateListNameById( 'ListId', 'ListName' );

#Delete a list from account
$client->lists->deleteList( 'ListId' );

#Subscribe or re-subscribe profiles to a list
$client->lists->addSubscribersToList( 'ListId', array $arrayOfProfiles );

#Check if profiles are on a list and not suppressed
$client->lists->checkListSubscriptions( 'ListId', array $emails, array $phoneNumbers, array $pushTokens );

#Unsubscribe and remove profiles from a list
$client->lists->deleteSubscribersFromList( 'ListId', array $emails );

#Add members to list without affecting consent status
$client->lists->addMembersToList( 'ListId', array $arrayOfProfiles );

#Check if profiles are on a list
$client->lists->checkListMembership( 'ListId', array $emails, array $phoneNumbers, array $pushTokens );

#Remove members from a list without changing their consent status
$client->lists->removeMembersFromList( 'ListId', array $emails );

#Get all exclusions on a list
$client->lists->getListExclusions( 'ListId' );

#Get all of the emails, phone numbers and push tokens for profiles in a given list or segment
$client->lists->getAllMembers( 'GroupId', $marker = $marker_value );
```

### You can fetch profile information given the profile ID, See here for more information https://www.klaviyo.com/docs/api/people
```php
#Get profile by profileId
$client->profiles->getProfile( 'ProfileId' );

#Update a profile
$client->profiles->updateProfile( 'ProfileId', array $properties );

#Get all metrics for a profile
$client->profiles->getAllProfileMetricsTimeline( 'ProfileId' );

#Get a specific metric for a profile
$client->profiles->getProfileMetricTimeline( 'ProfileId', 'MetricId' );

#Get a profile's ID by its email address
$client->profiles->getProfileIdByEmail('someone@mailinator.com');
```

### You can request a privacy-compliant profile deletion given an identifying property
```php
#Request profile deletion by email
$client->dataPrivacy->requestProfileDeletion('someone@mailinator.com');

#Request profile deletion by phone number
$client->dataPrivacy->requestProfileDeletion('1-234-567-8910', 'phone_number');

#Request profile deletion by person ID
$client->dataPrivacy->requestProfileDeletion('abc123', 'person_id');
```

## Exceptions

### Klaviyo\Exception\KlaviyoApiException
  Thrown when there is an issue making an API request. After you catch this exception, you can use getMessage() and it will return a string containing more details about the issue that occurred.

### Klaviyo\Exception\KlaviyoRateLimitException
  If a rate limit happens it will throw a Klaviyo\Exception\KlaviyoRateLimitException.
  After you catch this exception you can use getMessage() and it will return a JSON encoded array:
  `{"detail":"Request was throttled. Expected available in 26.0 seconds.","retryAfter":26}`

### Klaviyo\Exception\KlaviyoAuthenticationException
  Thrown when there is an authentication error when making an API request, usually caused by an invalid API key.

### Klaviyo\Exception\KlaviyoResourceNotFoundException
  Thrown when the system attempts to update a property that doesn't exist. For example, attempting to update a list that doesn't exist on the account.
