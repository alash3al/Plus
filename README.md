# Plus
An asynchronous I/O environment in pure PHP, in anthoer words, it is a micro nodejs implementation in pure PHP

# Example:
> a simple HTTP server using the new HTTPD class


```php
    include     "Plus.php";
    use         Plus\Frame;

    $frame  =   new Frame;
    $httpd  =   $frame->httpd();

    $httpd->createServer(function($request, $response)
    {
        $response->writeHead(200, ["Content-Type" => "text/html"]);
        $response->write("<!DOCTYPE 'html'>");
        $response->write("<html>");
        $response->write("<head>");
        $response->write("<title>Welcome to Plus http daemon</title>");
        $response->write("</head>");
        $response->write("<body>");
        $response->write("<h1>It works !!</h1>");
        $response->write("</body>");
        $response->write("</html>");
        $response->end();
    });

    $httpd->listen(80);
    $frame->run();
```

# Changelog:

### 1.0.1
* added `id` property to the `IOStream` class
* added `HTTPD` class as our official HTTP Daemon

### 1.0
initialized
