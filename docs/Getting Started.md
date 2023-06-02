# Getting Started

# Installation

```bash
composer require eru123/holyphp
```

# Autoloading Composer Dependencies

On your index file, assign the value from composer autoload to a variable.

```php
$autoload = require_once __DIR__ . '/vendor/autoload.php';
```

Use the Composer helper to pass the autoload variable, this will be used to load the configurations, routes, and other files.

```php
eru123\helper\Composer::set_autoload($autoload);
```

Or if you are lazy like the creator himself to type all that namespacing, you can use the `set_autoload` function directly.

```php
set_autoload($autoload);
```

Or if you are still surprisingly lazy, you can just (go fuck and die, just kidding we're all holy ones here) require the autoloader and it will do it itself when it's **needed**.

```php
require_once __DIR__ . '/vendor/autoload.php';
```

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

## Runtime Usage
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

