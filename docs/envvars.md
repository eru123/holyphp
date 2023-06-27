[Back to Main Page](../README.md)

# Loading Environment Variables
### From file
```php
eru123\config\DotEnv::load(__DIR__.'/.env');
```
### From Directory
This will look for `.env` file in the given directory
```php
eru123\config\DotEnv::load(__DIR__);
```
### Enable Strict Mode
Passing a `true` (`boolean`) value to the second argument of load function enables the strict mode
```php
eru123\config\DotEnv::load(__DIR__, true);
```

Enabling strict mode will do the following:
 - check if file or directory exists
 - check if variable name is valid
 - check if variable in the value is defined
 - throws an exception when parsing failed

# Runtime Usage
### Set env value
```php
$key = 'app.mode';
$value = 'development'
env_set($key, $value);
env_set('app.modes', [
    'development' => [
        'name' => 'Dev'
    ],
    'production' => [
        'name' => 'Dev'
    ],
]);
```
### Get env value
```php
$default = NULL; // default value in case the key is not defined
$mode = env('app.mode', $default); // development

// Using variables
$mode = env('app.modes.${app.mode}.name', $default); // Dev
```
# Tips
If you have environments for different stages, you can load first the default environment variables where the app env mode is defined and use it to load the environment variables for the current environment.

```php
use eru123\config\DotEnv;

DotEnv::load(__DIR__ . '/.env');
DotEnv::load(__DIR__ . '/' . env('APP_ENV') . '.env');
```