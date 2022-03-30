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

from:
```php
$client = new Klaviyo( 'PRIVATE_API_KEY', 'PUBLIC_API_KEY' );
```

from:
```php
$client = new Client('YOUR_API_KEY');
```

### Updating API Operations

The new API has many name changes to both namespace and parameters (types+format). Additionally, it only makes use of standard/built-in PHP types (int, string, array, etc), and does not make use of custom Models, as this one does. Please reference [this section](https://github.com/klaviyo/klaviyo-php-sdk#comprehensive-list-of-operations--parameters) of the new SDK repo for details on how to update each operation.
