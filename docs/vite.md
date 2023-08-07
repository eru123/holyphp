[Back to Main Page](../README.md)

# Vite Support
## Vite.js Config
In able to use the Vite integration with HolyPHP, you must delete the default `index.html` file in the vite project root folder and then edit your `vite.config.js` with the following code
```js
export default defineConfig({
    // ...other configs
    build: {
        // ...other configs
        manifest: true,
        rollupOptions: {
            input: {
                main: 'src/main.js'
            }
        }
    }
})
```
 - `manifest: true` - This will generate a manifest.json file in the dist folder, in which the HolyPHP will be dependent on.
 - There will be no `index.html` file generated in the dist folder, instead we will be using the `main.js` file as our entry point for the build.

## Using HolyPHP Router
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use eru123\router\Router;
use eru123\router\Context;
use eru123\router\Vite;

// create a router instance
$router = new Router();

// Development Mode
Vite::src(__DIR__ . '/<vite_app>/src'); // Specify the src folder of your vite project
Vite::public(__DIR__ . '/<vite_app>/public'); // Specify the public folder of your vite project
Vite::set('app_id', 'app'); // use the id of the root element in your app. the default is `app`, for react it is `root`
Vite::template('dev'); // use `react` as template if using reactjs

// Production Mode
Vite::dist(__DIR__ . '/<vite_app>/dist'); // when in production mode, you must specify the dist folder
Vite::manifest(__DIR__ . '/<vite_app>/dist/manifest.json'); // when in production mode, you must specify the manifest file, it should be in the dist folder
Vite::template('vite'); // for production use `vite` as template

// Set variables to the vite template
Vite::set('app_name', 'My App');
Vite::set('app_description', 'This is my app');

// create a route and use the vite() method to render the vite template
$router->get('/', function (Context $ctx) {
    Vite::head('<title>${app_name}</title>');
    Vite::head('<meta name="description" content="${app_description}">');
    return $ctx->vite();
});


// Inject the generated vite routes and middleware to the router with optional parameters, using optional parameters will override the default values set in the template
Vite::inject($router, [
    'app_name' => 'My App 2',
    'app_description' => 'This is my app 2'
]);

// handle the request
$router->run();
```

## Using Vite outside of HolyPHP Router
When doing this you must think or find a way to serve static files in your server.

### Development Mode
```php
use eru123\router\Vite;

Vite::src(__DIR__ . '/<vite_app>/src'); // Specify the src folder of your vite project
Vite::public(__DIR__ . '/<vite_app>/public'); // Specify the public folder of your vite project
Vite::set('app_id', 'app'); // use the id of the root element in your app. the default is `app`, for react it is `root`
Vite::template('dev'); // use `react` as template if using reactjs

/**
 * Directory Serve Mapping
 *  - <base_url>/ => <vite_app>/public
 *  - <base_url>/src => <vite_app>/src
 */

// (Optional) Do a template injection
Vite::set('app_name', 'My App');
Vite::set('app_description', 'This is my app');
Vite::head('<title>${app_name}</title>');
Vite::head('<meta name="description" content="${app_description}">');

// print the rendered template
echo Vite::render([
    'app_name' => 'My App',
    'app_description' => 'This is my app'
]);
exit;

```

### Production Mode
```php
use eru123\router\Vite;

Vite::dist(__DIR__ . '/<vite_app>/dist'); // when in production mode, you must specify the dist folder
Vite::manifest(__DIR__ . '/<vite_app>/dist/manifest.json'); // when in production mode, you must specify the manifest file, it should be in the dist folder
Vite::template('vite'); // for production use `vite` as template

/**
 * Directory Serve Mapping
 *  - <base_url>/ => <vite_app>/dist
 */

// (Optional) Do a template injection
Vite::head('<title>${app_name}</title>');
Vite::head('<meta name="description" content="${app_description}">');

// print the rendered template
echo Vite::render([
    'app_name' => 'My App',
    'app_description' => 'This is my app'
]);
exit;

```

## Rendering the Vite Template
 - `Vite::render(array $data = [])` - This will render the vite template with the given data. The data will be used to replace the variables in the template. This will also override the variables that are already set using the `Vite::set()` method.
 - `Vite::head(string $html)` - This will add the given html string to the head tag of the template.
 - `Vite::body(string $html)` - This will add the given html string to the body tag of the template.
 - `Vite::inject(Router $router, array $data = [])` - This will inject the vite routes and middleware to the `Router` instance with the given data. The data will be used to replace the variables in the template. This will also override the variables that are already set using the `Vite::set()` method.
 - `Vite::src(string $src)` - (dev mode) This will set the src folder of the vite project.
 - `Vite::public(string $public)` - (dev mode) This will set the public folder of the vite project.
 - `Vite::dist(string $dist)` - (prod mode) This will set the dist folder of the vite project.
 - `Vite::manifest(string $manifest)` - (prod mode) This will set the manifest file of the vite project.
 - `Vite::set(string $key, string $value)` - This will set the value of the given key. The key will be used to replace the variables in the template.