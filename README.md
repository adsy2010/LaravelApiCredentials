# Laravel Api Credentials

```php
$key = 'PUBLIC';
$value = 'TEST_VALUE_STRING';
$service = 'TEST_SERVICE';
$scopes = [];

$credentials = (new Credential)->store($key, $value, $service, $scopes);
```

```php
//Load a credential and call delete. This will remove the credential and any scopes attached.
$credential->delete();
```

```php
$service = 'Quickbooks';
$scopeNameArray = ['Publishable'];

(new Scope)->retrieveCredentialValue($service, $scopeNameArray, ScopeAccess::READ_AND_WRITE);
```
