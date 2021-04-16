## CHANGELOG
### [Unreleased]

### [2.3.0]
- Update - Add people/search endpoint.
- Update - Add data-privacy/deletion-request endpoint.
- Deprecate - Rename List API methods: getListDetails, updateListDetails, subscribeMembersToList, unsubscribeMembersFromList, getGroupMemberIdentifiers, getAllExclusionsOnList
- Deprecate - Rename Metric API methods: getMetricTimeline, exportMetricData

### 2.2.5
- Update - Add error details into KlaviyoAPI handleResponse function
- Update - Add KlaviyoApiException

### 2.2.4
- Fix - Instantiate KlaviyoRateLimitException properly
- Update - Add retryAfter as an array key for the KlaviyoRateLimitException message

### 2.2.3
- Fix - ProfileModel file to convert specialAttributes properties to camel case before executing property_exists method inside jsonSerialize

### 2.2.2
- Fix - KlaviyoAPI file to handle json_decode correctly when empty string is returned
- Fix - Fix EventModel unix timestamp issue
- Update - Add all Klaviyo special properties

### 2.2.1
- Fix - Composer file remove comma

### 2.2.0
- Fix - EventModel configuration to handle null dates
- Fix - PHP compatibility >= 5.4

### 2.1.1
- Fix - EventModel configuration to use DateTime correctly
- Fix - ProfileModel configuration to set special attributes correctly

### 2.1.0
- Utilize ext-curl and remove Guzzle dependency.
- Add KlaviyoResourceNotFoundException.

### 2.0.0
- Redesign of the modules pattern.  Instead of using client->method_name we now utilize client->BASE_API_ENDPOINT->method_name
- For example in 1.* `$client->getMetrics()` would now be `$client->metrics->getMetrics()` in 2.*
- There is no compatibility on migrating from 1.* to 2.* and will need to follow the new 2.0.0 pattern

### 1.0
- As this changelog was created after 2.0.0, please refer to the README for version 1 https://github.com/klaviyo/php-klaviyo/tree/d388ca998dff55b2a7e420c2c7aa2cd221365d1c

[Unreleased]: https://github.com/klaviyo/php-klaviyo/compare/2.3.0...HEAD
[2.3.0]: https://github.com/klaviyo/php-klaviyo/compare/2.2.5...2.3.0
