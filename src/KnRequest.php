<?php

declare(strict_types=1);

namespace Karewan\KnHttp;

use CurlHandle;
use RuntimeException;
use Throwable;

class KnRequest
{
	/**
	 * Response type constants
	 * @var int
	 */
	private const int
		RES_AS_STRING = 1,
		RES_AS_JSON = 2,
		RES_AS_FILE = 3,
		RES_AS_STREAM = 4;

	/**
	 * The CurlHandle
	 * @var null|CurlHandle
	 */
	private ?CurlHandle $curl;

	/**
	 * Connect timeout in seconds
	 * @var int
	 */
	private int $connectTimeout = 15;

	/**
	 * Timeout in seconds
	 * @var int
	 */
	private int $timeout = 270;

	/**
	 * User agent
	 * @var null|string
	 */
	private ?string $userAgent = 'KnHttp';

	/**
	 * Verify SSL
	 * @var bool
	 */
	private bool $verifySsl = true;

	/**
	 * Request HTTP method
	 * @var string
	 */
	private string $method = 'GET';

	/**
	 * Request URL
	 * @var string
	 */
	private string $url = '';

	/**
	 * Request basic auth
	 * @var null|string
	 */
	private ?string $basicAuth = null;

	/**
	 * Request headers
	 * @var array<string,string>
	 */
	private array $headers = [];

	/**
	 * Request path parameters
	 * @var array<string,string>
	 */
	private array $pathParams = [];

	/**
	 * Request query parameters
	 * @var array<string,string>
	 */
	private array $queryParams = [];

	/**
	 * CURL options
	 * @var array<int,mixed>
	 */
	private array $curlOptions = [];

	/**
	 * Form body
	 * @var null|array
	 */
	private ?array $formBody = null;

	/**
	 * Form data body
	 * @var null|array
	 */
	private ?array $formDataBody = null;

	/**
	 * String body
	 * @var null|string
	 */
	private ?string $stringBody = null;

	/**
	 * JSON body
	 * @var mixed
	 */
	private mixed $jsonBody = null;

	/**
	 * File body
	 * @var null|string
	 */
	private ?string $fileBody = null;

	/**
	 * Stream body
	 * @var null|resource
	 */
	private mixed $streamBody = null;

	/**
	 * Response headers
	 * @var array<string,string>
	 */
	private array $responseHeaders = [];

	/**
	 * The expected response type for this request
	 * @var int
	 */
	private int $responseType = self::RES_AS_STRING;

	/**
	 * Arguments for the response parser (e.g., for JSON decoding)
	 * @var array
	 */
	private array $responseTypeArgs = [];

	/**
	 * Response file path
	 * @var null|string
	 */
	private ?string $responseFile = null;

	/**
	 * Response stream
	 * @var null|resource
	 */
	private mixed $responseStream = null;

	/**
	 * Class constructor
	 * @return void
	 */
	public function __construct()
	{
		// Init CURL for single requests
		$this->curl = curl_init() ?: null;
		if (!isset($this->curl)) throw new RuntimeException('cURL handle not initialized.');

		// Set default handle CURL options if it exists
		curl_setopt_array($this->curl, [
			CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_HEADER => false,
			CURLOPT_FAILONERROR => false,
			CURLOPT_TCP_FASTOPEN => true,
			CURLOPT_TCP_NODELAY => true,
			CURLOPT_TCP_KEEPALIVE => true,
			CURLOPT_FORBID_REUSE => false,
			CURLOPT_SSL_VERIFYSTATUS => false,
			CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
			CURLOPT_NOSIGNAL => true,
			CURLOPT_HEADERFUNCTION => [$this, '_handleResponseHeader'],
			CURLOPT_ACCEPT_ENCODING => ''
		]);
	}

	/**
	 * Class destructor
	 * @return void
	 */
	public function __destruct()
	{
		// Close CURL handle if it exists
		if (isset($this->curl)) curl_close($this->curl);
	}

	/**
	 * GET Request
	 * @param string $url
	 * @return KnRequest
	 */
	public function get(string $url): KnRequest
	{
		return $this->request('GET', $url);
	}

	/**
	 * POST request
	 * @param string $url
	 * @return KnRequest
	 */
	public function post(string $url): KnRequest
	{
		return $this->request('POST', $url);
	}

	/**
	 * PUT request
	 * @param string $url
	 * @return KnRequest
	 */
	public function put(string $url): KnRequest
	{
		return $this->request('PUT', $url);
	}

	/**
	 * DELETE request
	 * @param string $url
	 * @return KnRequest
	 */
	public function delete(string $url): KnRequest
	{
		return $this->request('DELETE', $url);
	}

	/**
	 * PATCH Request
	 * @param string $url
	 * @return KnRequest
	 */
	public function patch(string $url): KnRequest
	{
		return $this->request('PATCH', $url);
	}

	/**
	 * Prepare a request
	 * @param string $method
	 * @param string $url
	 * @return KnRequest
	 */
	public function request(string $method, string $url): KnRequest
	{
		$this->method = $method;
		$this->url = strtok($url, '#');
		return $this;
	}

	/**
	 * Set verify SSL
	 * @param bool $verify
	 * @return KnRequest
	 */
	public function setVerifySsl(bool $verify): KnRequest
	{
		$this->verifySsl = $verify;
		return $this;
	}

	/**
	 * Set connect timeout in seconds
	 * @param int $timeout
	 * @return KnRequest
	 */
	public function setConnectTimeout(int $timeout): KnRequest
	{
		$this->connectTimeout = $timeout;
		return $this;
	}

	/**
	 * Set timeout in seconds
	 * @param int $timeout
	 * @return KnRequest
	 */
	public function setTimeout(int $timeout): KnRequest
	{
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * Set user agent
	 * @param null|string $userAgent
	 * @return KnRequest
	 */
	public function setUserAgent(?string $userAgent): KnRequest
	{
		$this->userAgent = $userAgent;
		return $this;
	}

	/**
	 * Set request headers
	 * @param array<string,string> $headers
	 * @return KnRequest
	 */
	public function setHeaders(array $headers): KnRequest
	{
		$this->headers = $headers;
		return $this;
	}

	/**
	 * Set request header
	 * @param string $key
	 * @param null|string $value (null delete the header)
	 * @return KnRequest
	 */
	public function setHeader(string $key, ?string $value): KnRequest
	{
		if (is_null($value)) unset($this->headers[$key]);
		else $this->headers[$key] = $value;
		return $this;
	}

	/**
	 * Clear request headers
	 * @return KnRequest
	 */
	public function clearHeaders(): KnRequest
	{
		$this->headers = [];
		return $this;
	}

	/**
	 * Set request path parameters
	 * @param array<string,string> $pathParams
	 * @return KnRequest
	 */
	public function setPathParams(array $pathParams): KnRequest
	{
		$this->pathParams = $pathParams;
		return $this;
	}

	/**
	 * Set request path parameter
	 * @param string $key
	 * @param null|string $value (null delete the param)
	 * @return KnRequest
	 */
	public function setPathParam(string $key, ?string $value): KnRequest
	{
		if (is_null($value)) unset($this->pathParams[$key]);
		else $this->pathParams[$key] = $value;
		return $this;
	}

	/**
	 * Clear request path parameters
	 * @return KnRequest
	 */
	public function clearPathParams(): KnRequest
	{
		$this->pathParams = [];
		return $this;
	}

	/**
	 * Set basic auth
	 * @param string $username
	 * @param string $password
	 * @return KnRequest
	 */
	public function setBasicAuth(string $username, string $password): KnRequest
	{
		$this->basicAuth = base64_encode("{$username}:{$password}");
		return $this;
	}

	/**
	 * Clear basic auth
	 * @return KnRequest
	 */
	public function clearBasicAuth(): KnRequest
	{
		$this->basicAuth = null;
		return $this;
	}

	/**
	 * Set request query parameters
	 * @param array<string,string> $queryParams
	 * @return KnRequest
	 */
	public function setQueryParams(array $queryParams): KnRequest
	{
		$this->queryParams = $queryParams;
		return $this;
	}

	/**
	 * Set request query parameter
	 * @param string $key
	 * @param null|string $value (null delete the param)
	 * @return KnRequest
	 */
	public function setQueryParam(string $key, ?string $value): KnRequest
	{
		if (is_null($value)) unset($this->queryParams[$key]);
		else $this->queryParams[$key] = $value;
		return $this;
	}

	/**
	 * Clear request query parameters
	 * @return KnRequest
	 */
	public function clearQueryParams(): KnRequest
	{
		$this->queryParams = [];
		return $this;
	}

	/**
	 * Set CURL options
	 * @param array<int,mixed> $options
	 * @return KnRequest
	 */
	public function setCurlOptions(array $options): KnRequest
	{
		$this->curlOptions = $options;
		return $this;
	}

	/**
	 * Set CURL option
	 * @param int $key
	 * @param mixed $value
	 * @return KnRequest
	 */
	public function setCurlOption(int $key, mixed $value): KnRequest
	{
		$this->curlOptions[$key] = $value;
		return $this;
	}

	/**
	 * Clear CURL options
	 * @return KnRequest
	 */
	public function clearCurlOptions(): KnRequest
	{
		$this->curlOptions = [];
		return $this;
	}

	/**
	 * Set form body
	 * @param null|array $form
	 * @return KnRequest
	 */
	public function setFormBody(?array $form): KnRequest
	{
		$this->clearBodies();
		$this->formBody = $form;
		return $this;
	}

	/**
	 * Set form data body
	 * @param null|array $form
	 * @return KnRequest
	 */
	public function setFormDataBody(?array $form): KnRequest
	{
		$this->clearBodies();
		$this->formDataBody = $form;
		return $this;
	}

	/**
	 * Set string body
	 * @param null|string $str
	 * @return KnRequest
	 */
	public function setStringBody(?string $str): KnRequest
	{
		$this->clearBodies();
		$this->stringBody = $str;
		return $this;
	}

	/**
	 * Set JSON body
	 * @param mixed $json
	 * @return KnRequest
	 */
	public function setJsonBody(mixed $json): KnRequest
	{
		$this->clearBodies();
		$this->jsonBody = $json;
		return $this;
	}

	/**
	 * Set file body path
	 * @param null|string $filePath
	 * @return KnRequest
	 */
	public function setFileBody(?string $filePath): KnRequest
	{
		$this->clearBodies();
		$this->fileBody = $filePath;
		return $this;
	}

	/**
	 * Set stream body
	 * @param null|resource $stream
	 * @return KnRequest
	 */
	public function setStreamBody(mixed $stream): KnRequest
	{
		$this->clearBodies();
		$this->streamBody = $stream;
		return $this;
	}

	/**
	 * Clear bodies
	 * @return KnRequest
	 */
	public function clearBodies(): KnRequest
	{
		$this->formBody = null;
		$this->formDataBody = null;
		$this->stringBody = null;
		$this->jsonBody = null;
		$this->fileBody = null;
		return $this;
	}

	/**
	 * Execute the request and parse as string
	 * @return KnResponse
	 */
	public function execForString(): KnResponse
	{
		$this->responseType = self::RES_AS_STRING;
		return $this->_execute();
	}

	/**
	 * Request as string
	 * @return KnRequest
	 */
	public function setForString(): KnRequest
	{
		$this->responseType = self::RES_AS_STRING;
		return $this;
	}

	/**
	 * Execute the request and parse as JSON
	 * @param bool $associative
	 * @param int $depth
	 * @param int $flags
	 * @return KnResponse
	 */
	public function execForJson(bool $associative = false, int $depth = 512, int $flags = JSON_BIGINT_AS_STRING): KnResponse
	{
		$this->responseType = self::RES_AS_JSON;
		$this->responseTypeArgs = ['associative' => $associative, 'depth' => $depth, 'flags' => $flags];
		return $this->_execute();
	}

	/**
	 * Request as JSON
	 * @param bool $associative
	 * @param int $depth
	 * @param int $flags
	 * @return KnRequest
	 */
	public function setForJson(bool $associative = false, int $depth = 512, int $flags = JSON_BIGINT_AS_STRING): KnRequest
	{
		$this->responseType = self::RES_AS_JSON;
		$this->responseTypeArgs = ['associative' => $associative, 'depth' => $depth, 'flags' => $flags];
		return $this;
	}

	/**
	 * Execute the request and put res data into the file
	 * @param string $filePath
	 * @return KnResponse
	 */
	public function execForFile(string $filePath): KnResponse
	{
		$this->responseType = self::RES_AS_FILE;
		$this->responseFile = $filePath;
		return $this->_execute();
	}

	/**
	 * Set for file
	 * @param string $filePath
	 * @return KnRequest
	 */
	public function setForFile(string $filePath): KnRequest
	{
		$this->responseType = self::RES_AS_FILE;
		$this->responseFile = $filePath;
		return $this;
	}

	/**
	 * Execute the request and put res data into the stream
	 * @param resource $fp
	 * @return KnResponse
	 */
	public function execForStream(mixed $fp): KnResponse
	{
		$this->responseType = self::RES_AS_STREAM;
		$this->responseStream = $fp;
		return $this->_execute();
	}

	/**
	 * Set for stream
	 * @param resource $fp
	 * @return KnRequest
	 */
	public function setForStream(mixed $fp): KnRequest
	{
		$this->responseType = self::RES_AS_STREAM;
		$this->responseStream = $fp;
		return $this;
	}

	/**
	 * Execute a single request.
	 * @return KnResponse
	 */
	private function _execute(): KnResponse
	{
		try {
			// Build the curl handle
			$handlesToClose = $this->_buildCurlHandle($this->curl);

			// Execute the request
			$response = curl_exec($this->curl);

			// Close opened handle for that request
			foreach ($handlesToClose as $h) {
				if (is_resource($h)) fclose($h);
			}

			// CURL error code
			$curlErrorNo = curl_errno($this->curl);
			$curlError = curl_error($this->curl);

			// Handle response
			$knResponse = $this->_parseResponse($this->curl, $response, $curlErrorNo, $curlError);

			// Free memory
			$this->responseHeaders = [];

			// Returns the res
			return $knResponse;
		} catch (Throwable $e) {
			return new KnResponse(
				error: KnResponse::ERROR_UNKNOWN,
				exception: trim($e->__toString())
			);
		}
	}

	/**
	 * Execute multiple requests in parallel
	 * @param array<KnRequest> $requests
	 * @param int $concurrency
	 * @param int $maxConnPerHost
	 * @return array<KnResponse>
	 */
	public static function execMulti(array $requests, int $concurrency = 10, int $maxConnPerHost = 1): array
	{
		$multiHandle = curl_multi_init();
		curl_multi_setopt($multiHandle, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
		curl_multi_setopt($multiHandle, CURLMOPT_MAX_HOST_CONNECTIONS, $maxConnPerHost);
		curl_multi_setopt($multiHandle, CURLMOPT_MAX_TOTAL_CONNECTIONS, $concurrency);

		$shareHandle = curl_share_init();
		curl_share_setopt($shareHandle, CURLSHOPT_SHARE, CURL_LOCK_DATA_DNS);
		curl_share_setopt($shareHandle, CURLSHOPT_SHARE, CURL_LOCK_DATA_SSL_SESSION);
		curl_share_setopt($shareHandle, CURLSHOPT_SHARE, CURL_LOCK_DATA_CONNECT);

		$handles = [];
		$results = [];

		// Prepare all handles and add them to the multi-handle
		foreach ($requests as $key => $req) {
			if (!$req instanceof self) continue;
			curl_setopt($req->curl, CURLOPT_SHARE, $shareHandle);
			$handlesToClose = $req->_buildCurlHandle($req->curl);
			curl_multi_add_handle($multiHandle, $req->curl);

			// Store the handle and its corresponding request object, key and handles to close
			$handles[] = ['request' => $req, 'key' => $key, 'handles_to_close' => $handlesToClose];
		}

		// Execute the requests
		do {
			do {
				$mrc = curl_multi_exec($multiHandle, $running);
			} while ($mrc === CURLM_CALL_MULTI_PERFORM);

			if ($running) {
				$rc = curl_multi_select($multiHandle, 0.01);
				if ($rc === -1) usleep(2000);
			}
		} while ($running > 0 && $mrc === CURLM_OK);

		// Process the results
		foreach ($handles as $h) {
			/** @var KnRequest $req */
			$req = $h['request'];
			$key = $h['key'];

			$responseContent = curl_multi_getcontent($req->curl);

			// Close opened handle for that request
			foreach ($h['handles_to_close'] as $h) {
				if (is_resource($h)) fclose($h);
			}

			// CURL error code
			$curlErrorNo = curl_errno($req->curl);
			$curlError = curl_error($req->curl);

			// Handle response
			$results[$key] = $req->_parseResponse($req->curl, $responseContent, $curlErrorNo, $curlError);

			// Free memory
			$req->responseHeaders = [];

			curl_multi_remove_handle($multiHandle, $req->curl);
			curl_close($req->curl);
		}

		curl_multi_close($multiHandle);
		curl_share_close($shareHandle);

		return $results;
	}

	/**
	 * Configures a cURL handle based on the object's properties.
	 * @param CurlHandle $curl The handle to configure.
	 * @return array Returns file handles that need to be closed.
	 */
	private function _buildCurlHandle(CurlHandle $curl): array
	{
		// Set basic CURL options
		curl_setopt_array($curl, [
			CURLOPT_URL => $this->_buildUrl(),
			CURLOPT_CUSTOMREQUEST => $this->method,
			CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
			CURLOPT_TIMEOUT => $this->timeout,
			CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
			CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
			CURLOPT_USERAGENT => $this->userAgent,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_INFILE => null,
			CURLOPT_POSTFIELDS => null,
			CURLOPT_POST => false,
			CURLOPT_PUT => false
		]);

		// Set custom CURL options
		if (count($this->curlOptions)) curl_setopt_array($curl, $this->curlOptions);

		// Header
		$headers = $this->headers;

		// Basic auth
		if (!is_null($this->basicAuth)) $headers['Authorization'] = "Basic {$this->basicAuth}";

		// Body content
		$fileBodyHandle = null;
		if (isset($this->formBody)) {
			curl_setopt_array($curl, [
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($this->formBody)
			]);
		} elseif (isset($this->formDataBody)) {
			curl_setopt_array($curl, [
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $this->formDataBody
			]);
		} elseif (isset($this->stringBody)) {
			curl_setopt_array($curl, [
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $this->stringBody
			]);
			if (!isset($headers['Content-Type'])) $headers['Content-Type'] = 'text/plain; charset=utf-8';
		} elseif (isset($this->jsonBody)) {
			curl_setopt_array($curl, [
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($this->jsonBody)
			]);
			if (!isset($headers['Content-Type'])) $headers['Content-Type'] = 'application/json; charset=utf-8';
		} elseif (isset($this->fileBody)) {
			$fileBodyHandle = fopen($this->fileBody, 'rb');
			curl_setopt_array($curl, [
				CURLOPT_PUT => true,
				CURLOPT_INFILE => $fileBodyHandle,
				CURLOPT_INFILESIZE => filesize($this->fileBody)
			]);
		} elseif (isset($this->streamBody)) {
			curl_setopt_array($curl, [
				CURLOPT_PUT => true,
				CURLOPT_INFILE => $this->streamBody,
				CURLOPT_INFILESIZE => fstat($this->streamBody)['size']
			]);
		}

		// Set headers
		curl_setopt(
			$curl,
			CURLOPT_HTTPHEADER,
			array_map(fn($k, $v) => $this->_normalizeHeaderKey($k) . ": {$v}", array_keys($headers), array_values($headers))
		);

		// Handle response writing for file
		$downloadFileHandle = null;
		if ($this->responseType === self::RES_AS_FILE) {
			$downloadFileHandle = fopen($this->responseFile, 'wb');
			curl_setopt_array($curl, [
				CURLOPT_RETURNTRANSFER => false, // Write directly to file
				CURLOPT_FILE => $downloadFileHandle
			]);
		}
		// Handle response writing for stream
		else if ($this->responseType === self::RES_AS_STREAM) {
			curl_setopt_array($curl, [
				CURLOPT_RETURNTRANSFER => false, // Write directly to stream
				CURLOPT_FILE => $this->responseStream
			]);
		}

		return ['upload_handle' => $fileBodyHandle, 'download_handle' => $downloadFileHandle];
	}

	/**
	 * Parses a completed cURL response.
	 * @param CurlHandle $curl The completed handle.
	 * @param null|bool|string $response The response content from curl_exec or curl_multi_getcontent.
	 * @param int $curlErrorNo The curl error number.
	 * @param string $curlError The curl error message.
	 * @return KnResponse
	 */
	private function _parseResponse(CurlHandle $curl, null|bool|string $response, int $curlErrorNo, string $curlError): KnResponse
	{
		// Handle CURL errors
		switch ($curlErrorNo) {
			// No errors (except maybe the HTTP code)
			case CURLE_OK:
				return $this->_parseOkResponse($curl, $response);
				break;

			// Network error
			case CURLE_COULDNT_RESOLVE_HOST:
			case CURLE_COULDNT_CONNECT:
			case CURLE_OPERATION_TIMEOUTED:
			case CURLE_HTTP_PORT_FAILED:
			case CURLE_SEND_ERROR:
			case CURLE_RECV_ERROR:
				return new KnResponse(error: KnResponse::ERROR_NETWORK, curlError: $curlError);
				break;

			// SSL error
			case CURLE_SSL_CERTPROBLEM:
			case CURLE_SSL_CIPHER:
			case CURLE_SSL_PEER_CERTIFICATE:
			case CURLE_SSL_CONNECT_ERROR:
			case CURLE_SSL_ENGINE_NOTFOUND:
			case CURLE_SSL_ENGINE_SETFAILED:
			case CURLE_SSL_CACERT_BADFILE:
			case CURLE_SSL_CACERT:
				return new KnResponse(error: KnResponse::ERROR_SSL, curlError: $curlError);
				break;

			// Unknown error
			default:
				return new KnResponse(error: KnResponse::ERROR_UNKNOWN, curlError: $curlError);
				break;
		}
	}

	/**
	 * Parse OK Response
	 * @param CurlHandle $curl
	 * @param null|string|bool $response
	 * @return KnResponse
	 */
	private function _parseOkResponse(CurlHandle $curl, null|string|bool $response): KnResponse
	{
		$httpCode = intval(curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
		$data = null;

		// JSON
		if ($this->responseType === self::RES_AS_JSON) {
			$data = json_decode((string)$response, $this->responseTypeArgs['associative'], $this->responseTypeArgs['depth'], $this->responseTypeArgs['flags']);
			if (json_last_error() !== JSON_ERROR_NONE) {
				return new KnResponse(httpCode: $httpCode, headers: $this->responseHeaders, error: KnResponse::ERROR_PARSING, curlError: json_last_error_msg());
			}
		}
		// String, File or Stream
		else {
			$data = $response;
		}

		// Returns the response
		return new KnResponse(
			httpCode: $httpCode,
			headers: $this->responseHeaders,
			data: $data
		);
	}

	/**
	 * Get the prepared URL
	 * @return string
	 */
	private function _buildUrl(): string
	{
		$url = $this->url;

		// path parameters
		if (count($this->pathParams)) $url = str_replace(array_map(fn($k) => "{{$k}}", array_keys($this->pathParams)), $this->pathParams, $url);

		// query parameters
		if (count($this->queryParams)) $url .= (!empty(parse_url($url, PHP_URL_QUERY)) ? '&' : '?') . http_build_query($this->queryParams);

		return $url;
	}

	/**
	 * Normalize a header key
	 * @param string $k
	 * @return string
	 */
	private function _normalizeHeaderKey(string $k): string
	{
		return str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $k))));
	}

	/**
	 * Handle a response header
	 * @param CurlHandle $curl
	 * @param string $header
	 * @return int
	 */
	private function _handleResponseHeader(CurlHandle $curl, string $header): int
	{
		if ($pos = strpos($header, ':')) {
			$this->responseHeaders[$this->_normalizeHeaderKey(substr($header, 0, $pos))] = trim(substr($header, $pos + 2));
		}

		return strlen($header);
	}
}
