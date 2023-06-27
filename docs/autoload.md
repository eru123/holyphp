[Back to Main Page](../README.md)

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