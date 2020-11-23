## What is Klaviyo?

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
    
$client->publicAPI->track( $event );    
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

$client->publicAPI->track( $event );
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
    
$client->publicAPI->identify( $profile );
```

### You can get metrics, a timeline of events and export analytics for a metric. See here for more https://www.klaviyo.com/docs/api/metrics

```php
#return a list of all metrics in your Klaviyo account
$client->metrics->getMetrics();

#return a timeline of all metrics
$client->metrics->getMetricsTimeline();

#return a specific metric timeline using its metric ID
$client->metrics->getMetricTimeline( 'METRICID' );

#export metric specific values
$client->metrics->exportMetricData( 'METRICID' );
``` 

### You can create, update, read, and delete lists.  See here for more information https://www.klaviyo.com/docs/api/v2/lists
```php
#create a list
$client->lists->createList( 'List Name' );

#Get all lists in your Klaviyo account
$client->lists->getLists();

#Get information about a list
$client->lists->getListDetails( 'ListId' );

#update a lists properties
$client->lists->updateListDetails( 'ListId', 'ListName' );

#Delete a list from account
$client->lists->deleteList( 'ListId' );

#Subscribe or re-subscribe profiles to a list
$client->lists->subscribeMemberstoList( 'ListId', array $arrayOfProfiles );

#Check if profiles are on a list and not suppressed
$client->lists->checkListSubscriptions( 'ListId', array $emails, array $phoneNumbers, array $pushTokens );

#Unsubscribe and remove profiles from a list
$client->lists->unsubscribeMembersFromList( 'ListId', array $emails );

#Add members to list without affecting consent status
$client->lists->addMembersToList( 'ListId', array $arrayOfProfiles );

#Check if profiles are on a list
$client->lists->checkListMembership( 'ListId', array $emails, array $phoneNumbers, array $pushTokens );

#Remove members from a list without changing their consent status
$client->lists->removeMembersFromList( 'ListId', array $emails );

#Get all exclusions on a list
$client->lists->getAllExclusionsOnList( 'ListId' );

#Get all of the emails, phone numbers and push tokens for profiles in a given list or segment
$client->lists->getGroupMemberIdentifiers( 'GroupId' );
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
```

## Rate Limiting
  If a rate limit happens it will throw a Klaviyo/Exception/KlaviyoRateLimitException.
  You will use getMessage() to get the detail key with a string value mentioning the time to back off in seconds.
