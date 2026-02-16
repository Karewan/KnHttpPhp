# KnHttpPhp

Simple HTTP Client for PHP 8.3+ using curl

## Installation

### Requirements

PHP 8.3+

### Getting started

```shell
$ composer require karewan/knhttp
```

## Usage

### GET request

```php
/** @var KnResponse */
$res = (new KnRequest())
	->get("https://jsonplaceholder.typicode.com/todos/1")
	->execForJson();
```

### POST request

```php
/** @var KnResponse */
$res = (new KnRequest())
	->post("https://jsonplaceholder.typicode.com/posts")
	->setJsonBody([
		'title' => 'foo',
		'body' => 'bar',
		'userId' => 1
	])
	->execForJson();
```

### PUT request

```php
/** @var KnResponse */
$res = (new KnRequest())
	->put("https://jsonplaceholder.typicode.com/posts/1")
	->setJsonBody([
		'title' => 'foo',
		'body' => 'bar',
		'userId' => 1
	])
	->execForJson();
```

### DELETE request

```php
/** @var KnResponse */
$res = (new KnRequest())
	->delete("https://jsonplaceholder.typicode.com/todos/1")
	->execForJson();
```

### PATCH request

```php
/** @var KnResponse */
$res = (new KnRequest())
	->patch("https://jsonplaceholder.typicode.com/todos/1")
	->execForJson();
```

### Request

```php
/** @var KnResponse */
$res = (new KnRequest())
	->request("DELETE", "https://jsonplaceholder.typicode.com/todos/1")
	->execForJson();
```

### Parallel requests execution (use setFor instead of execFor)

```php
$req1 = (new KnRequest())
	->get("https://jsonplaceholder.typicode.com/todos/1")
	->setForJson();

$req2 = (new KnRequest())
	->get("https://jsonplaceholder.typicode.com/todos/2")
	->setForJson();

/** @var KnResponse[] */
$results = KnRequest::execMulti([$req1, $req2]);
```

### Request params
```php
// Enable verify SSL
$req->setVerifySsl(true);

// Request timeout in seconds
$req->setConnectTimeout(10);

// Set request timeout in seconds
$req->setTimeout(300);

// Set user agent
$req->setUserAgent("MyApp/1.0.0");

// Adding one header to the request
$req->setHeader("Api-Key", "xxx");

// Adding multi headers to the request
$req->setHeaders([
	"Api-Key" => "xxx",
	"X-Proto" => "CustomProto"
]);

// Remove all added headers from the request
$req->clearHeaders();

// Set a path param (https://jsonplaceholder.typicode.com/todos/{id})
$req->setPathParam("id", "1");

// Set multiples path param (https://jsonplaceholder.typicode.com/{type}/{id})
$req->setPathParams([
	"type" => "todos",
	"id" => "1"
]);

// Remove all added path params from the request
$req->clearPathParams();

// Set basic auth
$req->setBasicAuth("username", "password");

// Remove basic auth
$req->clearBasicAuth();

// Set a query param to be added to the URL
$req->setQueryParam("limit", "10");

// Set multiple query param to be added to the URL
$req->setQueryParams([
	"offset" => "10",
	"limit" => "10"
]);

// Remove all added query params from the request
$req->clearQueryParams();

// Set a CURL option
$req->setCurlOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

// Set multiple CURL option
$req->setCurlOptions([
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2
]);

// Remove all added curl options from the request
$req->clearCurlOptions();

// Set an URL encoded form body for POST and PUT
$req->setFormBody([
	"data1" => "xxx",
	"field3" => "xxx"
]);

// Set an formData body for POST and PUT
$req->setFormDataBody([
	"data1" => "xxx",
	"field3" => "xxx"
]);

// Set an string body for POST and PUT
$req->setStringBody("mybody");

// Set an JSON body for POST and PUT
$req->setJsonBody([
	"jsonField1" => "xxx",
	"keyboard" => [
		"azerty" => "bad for english",
		"qwerty" => "bad for french"
	]
])

// Set an file body for POST and PUT
$req->setFileBody("myfile.txt");

// Set an stream body for POST and PUT
$req->setStreamBody($mystream);

// Remove all added bodies from the request
$req->clearBodies();

/** @var KnResponse */
$req->execForString();

// Set request to a string response (for execMulti)
$req->setForString();

/** @var KnResponse */
$req->execForJson();

// Set request to a JSON response (for execMulti)
$req->setForJson();

/** @var KnResponse */
$req->execForFile();

// Set request to a file response (for execMulti)
$req->setForFile();

/** @var KnResponse */
$req->execForStream();

// Set request to a stream response (for execMulti)
$req->setForStream();
```

## Changelog

See the changelog [here](CHANGELOG.md)

## License

See the license [here](LICENSE.txt)

```
Copyright © 2025 - 2026 Florent VIALATTE (github.com/Karewan)

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```
