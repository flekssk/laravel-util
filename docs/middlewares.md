# Middlewares

## Register and use middleware

### Http middlewares


You can apply middleware without registers, just add it into route definitions like this:

```PHP
Route::middleware(YourMiddleware::class)->group(function () {
    Route::get('name', 'Api\V1\Controller@action');
});
```

Second way apply it to the route:

```PHP
Route::get('name', 'Api\V1\Controller@action')->middleware(YourMiddleware::class);
```

You can register middleware as alias, just register it in `\App\Http\Kernel` class in `protected $routeMiddleware = []` 
like this:

```PHP
protected $routeMiddleware = [
    'yourMiddleware' => \App\Http\Middleware\YourMiddleware::class,
];
```

And use alias name instead ::class

```PHP
Route::middleware('yourMiddleware')->group(function () {
    Route::get('name', 'Api\V1\Controller@action');
});
```

Second way apply it to the route:

```PHP
Route::get('name', 'Api\V1\Controller@action')->middleware('yourMiddleware');
```

## FKS-api-php-utils middlewares

### HttpCacheMiddleware

Middleware to pass cache header into response. By default, cache time is 3600 seconds, you can create 
`config\http-response-cache.php` and define cache time by `max-age` key

```PHP
<?php

return [
    'max-age' => 3600
];
```

### DebugbarMiddleware

Middleware to pass debugbar into response. By default, debugbar is disabled. you can enable it by adding DEBUGBAR_ENABLED=true to .env (env.staging) file and pass ?debugbar=true to the api url as a GET parameter. It will return debugbar data with json response.

```ENV
DEBUGBAR_ENABLED=true
```

```RESPONSE EXAMPLE
{
    ...,
    "_debugbar": {
        ...
    }
}
```

