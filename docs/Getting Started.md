# Getting Started

### Installation

```bash
composer require eru123/holyphp
```

### Initialization

On your index file, assign the value from composer autoload to a variable.

```php
$autoload = require_once __DIR__ . '/vendor/autoload.php';
```

Use the Composer helper to pass the autoload variable, this will be used to load the configurations, routes, and other files.

```php
eru123\helper\Composer::set_autoload($autoload);
```