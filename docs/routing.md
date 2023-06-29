[Back to Main Page](../README.md)

# Routing
Please do note that routing proposes 2 Router as follows:
 - Object based Router where the main router should be instantiated and used as an object.
 - Static based Router where the main router should be used as a static class.
To deep more on the topic let's first discuss about the logic behind the parent-child based routing.
### Understanding the Parent-Child Based Routing
To use a parent-child based routing we must use the object-based routing in able for us to define child routes for both object based and static based routing.

```php
use eru123\router\Router as ChildRouter;
use eru123\router\Router;
```