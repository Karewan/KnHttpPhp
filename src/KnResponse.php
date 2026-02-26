<?php

declare(strict_types=1);

namespace Karewan\KnHttp;

class KnResponse
{
	/**
	 * Indicates that there are no errors
	 * @var int
	 */
	public const int ERROR_NONE = 0;

	/**
	 * Indicates that an unknown error has occurred
	 */
	public const int ERROR_UNKNOWN = 1;

	/**
	 * Indicates that a network-related error has occurred
	 */
	public const int ERROR_NETWORK = 2;

	/**
	 * Indicates that an SSL-related error has occurred
	 */
	public const int ERROR_SSL = 3;

	/**
	 * Indicates that an HTTP status error has occurred (the status should be ‘2XX’)
	 */
	public const int ERROR_HTTP = 4;

	/**
	 * Indicates that an error occurred while parsing the response data (e.g. incorrect JSON)
	 */
	public const int ERROR_PARSING = 5;

	/**
	 * Response HTTP code (0 == error)
	 * @var int
	 */
	private int $httpCode;

	/**
	 * Class constructor
	 * @param array $curlInfo
	 * @param array<string,string> $headers Headers
	 * @param mixed $data Data
	 * @param int $error Error code (0 == no error)
	 * @param null|string $exception Exception full stack trace (null == no exception)
	 * @param null|string $curlError CURL error message (null == no error)
	 * @return void
	 */
	public function __construct(
		private array $curlInfo = [],
		private array $headers = [],
		private mixed $data = null,
		private int   $error = self::ERROR_NONE,
		private ?string $exception = null,
		private ?string $curlError = null
	) {
		// The HTTP Code
		$this->httpCode = intval($this->curlInfo['http_code'] ?? 0);

		// If no errors are detected, verify that the HTTP status code is ‘2XX’.
		if ($error === self::ERROR_NONE && ($this->httpCode < 200 || $this->httpCode >= 300)) {
			$this->error = self::ERROR_HTTP;
		}
	}

	/**
	 * Returns `true` if there are no errors
	 * @return bool
	 */
	public function isSuccessful(): bool
	{
		return $this->error === self::ERROR_NONE;
	}

	/**
	 * Retrurns the HTTP code (0 == error)
	 * @return int
	 */
	public function getHttpCode(): int
	{
		return $this->httpCode;
	}

	/**
	 * Retrurns the headers
	 * @return array<string,string>
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * Retrurns the datas
	 * @return mixed
	 */
	public function getData(): mixed
	{
		return $this->data;
	}

	/**
	 * Returns the last error code (0 == no error)
	 * @return int
	 */
	public function getError(): int
	{
		return $this->error;
	}

	/**
	 * Returns the exception full stack trace (null == no exception)
	 * @return null|string
	 */
	public function getException(): ?string
	{
		return $this->exception;
	}

	/**
	 * Returns the CURL error (null == no error)
	 * @return null|string
	 */
	public function getCurlError(): ?string
	{
		return $this->curlError;
	}

	/**
	 * Returns the CURL info regarding this Response
	 * @return array
	 */
	public function getCurlInfo(): array
	{
		return $this->curlInfo;
	}

	/**
	 * Returns total time of transfer in milliseconds
	 * @return int
	 */
	public function getTotalTime(): int
	{
		return intval(floatval($this->curlInfo['total_time'] ?? 0) * 1000);
	}
}
