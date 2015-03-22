# Plus
An asynchronous I/O environment in pure PHP, in anthoer words, it is a micro NodeJS implementation in pure PHP

# Example:
> a simple HTTP server that prints "Hello" . 


```php
include "Plus.php";

use Plus\Frame;

$app    =   new Frame;
$server =   $app->ioserver();

$server->on("connection", function($client){
    $client->on('data', function($data) use($client) {
        $client->write("HTTP/1.1 200 OK\r\nContent-Type: text/html;\r\nServer: Plus/1.0\r\n\r\n<h1>Hello</h1></h1>");
    });
    $client->on("drain", function($client){
        $client->close();
    });
    $client->on('error', function($e, $client){
        $client->close();
    });
});

$server->listen(8080);
$app->run();
```
