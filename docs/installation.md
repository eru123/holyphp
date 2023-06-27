[Back to Main Page](../README.md)

# Installation
### System Requirements
 - composer
 - php 8.1
 - bcmath or gmp for Big Integers (Optional)

### Via Composer as Package
```bash
composer require eru123/holyphp
```
### Via Composer Autoloading
In order to this you must first download a copy of the repository and put it somewhere in your project. Then configure you composer json using the format below:

```json
{
    ...
    "require": {
        "php": "^8.1"
    },
    "autoload": {
        "psr-4": {
            ...
            "eru123\\": "PathToRepository/eru123/"
        },
        "files": [
            "PathToRepository/eru123/holyf.php"
        ]
    },
    ...
}

```