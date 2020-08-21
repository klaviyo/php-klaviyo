## CHANGELOG

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