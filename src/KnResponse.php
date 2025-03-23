<?php

declare(strict_types=1);

namespace Karewan\KnHttp;

class KnResponse
{
	/**
	 * Error code
	 * @var int
	 */
	public const int
		ERROR_UNKNOWN = 1,
		ERROR_NETWORK = 2,
		ERROR_SSL = 3,
		ERROR_HTTP = 4,
		ERROR_PARSING = 5;

	/**
	 * Class constructor
	 * @param int $httpCode HTTP code (0 == error)
	 * @param array $headers Headers
	 * @param mixed $data Data
	 * @param int $error Error code (0 == no error)
	 * @param null|string $exception Exception full stack trace (null == no exception)
	 * @param null|string $curlError CURL error message (null == no error)
	 * @return void
	 */
	public function __construct(
		private int $httpCode = 0,
		private array $headers = [],
		private mixed $data = null,
		private int $error = 0,
		private ?string $exception = null,
		private ?string $curlError = null
	) {
		// HTTP error
		if (!$error && ($this->httpCode < 200 || $this->httpCode >= 300)) $this->error = self::ERROR_HTTP;
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
}
