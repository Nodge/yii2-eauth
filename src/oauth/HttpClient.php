<?php
/**
 * HttpClient class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\oauth;

use Yii;
use OAuth\Common\Http\Client\AbstractClient;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\UriInterface;

/**
 * Client implementation for cURL
 */
class HttpClient extends AbstractClient
{

	/**
	 *  If true, explicitly sets cURL to use SSL version 3. Use this if cURL
	 *  compiles with GnuTLS SSL.
	 *
	 * @var bool
	 */
	protected $forceSSL3 = false;

	/**
	 * If true and you are working in safe_mode environment or inside open_basedir
	 * it will use streams instead of curl.
	 *
	 * @var bool
	 */
	protected $useStreamsFallback = false;

	/**
	 * @var UriInterface
	 */
	protected $endpoint;

	/**
	 * @var mixed
	 */
	protected $requestBody;

	/**
	 * @var array
	 */
	protected $extraHeaders = array();

	/**
	 * @var string
	 */
	protected $method = 'POST';

	/**
	 * @param bool $force
	 */
	public function setForceSSL3($force)
	{
		$this->forceSSL3 = $force;
	}

	/**
	 * @return boolean
	 */
	public function getForceSSL3()
	{
		return $this->forceSSL3;
	}

	/**
	 * @param bool $useStreamsFallback
	 */
	public function setUseStreamsFallback($useStreamsFallback)
	{
		$this->useStreamsFallback = $useStreamsFallback;
	}

	/**
	 * @return bool
	 */
	public function getUseStreamsFallback()
	{
		return $this->useStreamsFallback;
	}

	/**
	 * Any implementing HTTP providers should send a request to the provided endpoint with the parameters.
	 * They should return, in string form, the response body and throw an exception on error.
	 *
	 * @param UriInterface $endpoint
	 * @param mixed $requestBody
	 * @param array $extraHeaders
	 * @param string $method
	 * @return string
	 */
	public function retrieveResponse(UriInterface $endpoint, $requestBody, array $extraHeaders = array(), $method = 'POST')
	{
		$this->endpoint = $endpoint;
		$this->requestBody = $requestBody;
		$this->extraHeaders = $extraHeaders;
		$this->method = $method;

		if ($this->useStreamsFallback && !$this->allowFollowLocation()) {
			return $this->streams();
		}

		return $this->curl();
	}

	/**
	 * @return bool
	 */
	protected function allowFollowLocation()
	{
		return !ini_get('safe_mode') && !ini_get('open_basedir');
	}

	/**
	 *
	 */
	protected function prepareRequest()
	{
		$this->method = strtoupper($this->method);
		$this->normalizeHeaders($this->extraHeaders);

		if ($this->method === 'GET' && !empty($this->requestBody)) {
			throw new \InvalidArgumentException('No body expected for "GET" request.');
		}

		if (!isset($this->extraHeaders['Content-type']) && $this->method === 'POST' && is_array($this->requestBody)) {
			$this->extraHeaders['Content-type'] = 'Content-type: application/x-www-form-urlencoded';
		}

		// Some of services requires User-Agent header (e.g. GitHub)
		if (!isset($this->extraHeaders['User-Agent'])) {
			$this->extraHeaders['User-Agent'] = 'User-Agent: yii2-eauth';
		}

		$this->extraHeaders['Host'] = 'Host: ' . $this->endpoint->getHost();
		$this->extraHeaders['Connection'] = 'Connection: close';

		if (YII_DEBUG) {
			Yii::trace('EAuth http request: ' . PHP_EOL . var_export(array(
					'url' => $this->endpoint->getAbsoluteUri(),
					'method' => $this->method,
					'headers' => $this->extraHeaders,
					'body' => $this->requestBody,
				), true), __NAMESPACE__);
		}

		if (is_array($this->requestBody)) {
			$this->requestBody = http_build_query($this->requestBody, null, '&');
		}
	}

	/**
	 * @return string
	 * @throws TokenResponseException
	 */
	protected function curl()
	{
		$this->prepareRequest();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->endpoint->getAbsoluteUri());

		if ($this->method === 'POST' || $this->method === 'PUT') {
			if ($this->method === 'PUT') {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			} else {
				curl_setopt($ch, CURLOPT_POST, true);
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestBody);
		} else {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
		}

		if ($this->allowFollowLocation() && $this->maxRedirects > 0) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, $this->maxRedirects);
		}

		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->extraHeaders);

		if ($this->forceSSL3) {
			curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		}

		$response = curl_exec($ch);
		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if (YII_DEBUG) {
			Yii::trace('EAuth http response: ' . PHP_EOL . var_export($response, true), __NAMESPACE__);
		}

		if (false === $response) {
			$errNo = curl_errno($ch);
			$errStr = curl_error($ch);
			curl_close($ch);

			if (empty($errStr)) {
				$errStr = 'Failed to request resource.';
			} else {
				$errStr = 'cURL Error # ' . $errNo . ': ' . $errStr;
			}

			Yii::error('EAuth curl error (' . $responseCode . '): ' . $errStr, __NAMESPACE__);
			throw new TokenResponseException($errStr, $responseCode);
		}

		curl_close($ch);

		return $response;
	}

	/**
	 * @return string
	 * @throws TokenResponseException
	 */
	protected function streams()
	{
		$this->prepareRequest();

		$context = stream_context_create(array(
			'http' => array(
				'method' => $this->method,
				'header' => array_values($this->extraHeaders),
				'content' => $this->requestBody,
				'protocol_version' => '1.1',
				'user_agent' => 'Yii2 EAuth Client',
				'max_redirects' => $this->maxRedirects,
				'timeout' => $this->timeout,
			),
		));

		$level = error_reporting(0);
		$response = file_get_contents($this->endpoint->getAbsoluteUri(), false, $context);
		error_reporting($level);

		if (YII_DEBUG) {
			Yii::trace('EAuth http response: ' . PHP_EOL . var_export($response, true), __NAMESPACE__);
		}

		if (false === $response) {
			$lastError = error_get_last();

			if (is_null($lastError)) {
				$errStr = 'Failed to request resource.';
			} else {
				$errStr = $lastError['message'];
			}

			Yii::error('EAuth streams error: ' . $errStr, __NAMESPACE__);
			throw new TokenResponseException($errStr);
		}

		return $response;
	}

}
