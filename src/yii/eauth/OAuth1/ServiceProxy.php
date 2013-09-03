<?php
/**
 * OAuth1 ServiceProxy class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace yii\eauth\oauth1;

use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth1\Service\AbstractService;
use OAuth\OAuth1\Signature\SignatureInterface;
use OAuth\OAuth1\Token\StdOAuth1Token;
use yii\eauth\ErrorException;

class ServiceProxy extends AbstractService {

	/**
	 * @var Service the currently used service class
	 */
	protected $service;

	/**
	 * @param Credentials $credentials
	 * @param ClientInterface $httpClient
	 * @param TokenStorageInterface $storage
	 * @param SignatureInterface $signature
	 * @param UriInterface $baseApiUri
	 * @param Service $service
	 * @throws ErrorException
	 */
	public function __construct(Credentials $credentials, ClientInterface $httpClient, TokenStorageInterface $storage, SignatureInterface $signature, UriInterface $baseApiUri = null, Service $service = null) {
		parent::__construct($credentials, $httpClient, $storage, $signature, $baseApiUri);

		if (!isset($service)) {
			throw new ErrorException('Service argument is required.');
		}

		$this->service = $service;
	}

	/**
	 * @return string
	 */
	public function service() {
		return $this->service->getServiceName();
	}

	/**
	 * @return StdOAuth1Token
	 */
	public function retrieveAccessToken() {
		return $this->storage->retrieveAccessToken($this->service());
	}

	/**
	 *
	 */
	public function hasValidAccessToken() {
		$serviceName = $this->service();

		if (!$this->storage->hasAccessToken($serviceName)) {
			return false;
		}

		/** @var $token StdOAuth1Token */
		$token = $this->storage->retrieveAccessToken($serviceName);
		return $this->checkTokenLifetime($token);
	}

	/**
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function checkTokenLifetime($token) {
		// assume that we have at least a minute to execute a queries.
		return $token->getEndOfLife() - 60 > time()
			|| $token->getEndOfLife() === TokenInterface::EOL_NEVER_EXPIRES;
	}

	/**
	 * @return null|TokenInterface
	 */
	public function getAccessToken() {
		if (!$this->hasValidAccessToken()) {
			return null;
		}

		$serviceName = $this->service();
		return $this->storage->retrieveAccessToken($serviceName);
	}

	/**
	 * @return UriInterface
	 */
	public function getRequestTokenEndpoint() {
		return new Uri($this->service->getRequestTokenEndpoint());
	}

	/**
	 * @return UriInterface
	 */
	public function getAuthorizationEndpoint() {
		return new Uri($this->service->getAuthorizationEndpoint());
	}

	/**
	 * @return UriInterface
	 */
	public function getAccessTokenEndpoint() {
		return new Uri($this->service->getAccessTokenEndpoint());
	}

	/**
	 * We need a separate request token parser only to verify the `oauth_callback_confirmed` parameter. For the actual
	 * parsing we can just use the default access token parser.
	 *
	 * @param string $responseBody
	 * @return StdOAuth1Token
	 * @throws TokenResponseException
	 */
	protected function parseRequestTokenResponse($responseBody) {
		parse_str($responseBody, $data);

		if (!isset($data) || !is_array($data)) {
			throw new TokenResponseException('Unable to parse response.');
		}
		else if (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] != 'true') {
			throw new TokenResponseException('Error in retrieving token.');
		}

		return $this->parseAccessTokenResponse($responseBody);
	}

	/**
	 * @param string $responseBody
	 * @return StdOAuth1Token
	 * @throws TokenResponseException
	 */
	protected function parseAccessTokenResponse($responseBody) {
		parse_str($responseBody, $data);

		if (!isset($data) || !is_array($data)) {
			throw new TokenResponseException('Unable to parse response.');
		}
		else if (isset($data['error'])) {
			throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
		}

		$token = new StdOAuth1Token();
		$names = $this->service->getAccessTokenArgumentNames();

		$token->setRequestToken($data[$names['oauth_token']]);
		$token->setRequestTokenSecret($data[$names['oauth_token_secret']]);
		$token->setAccessToken($data[$names['oauth_token']]);
		$token->setAccessTokenSecret($data[$names['oauth_token_secret']]);
		unset($data[$names['oauth_token']], $data[$names['oauth_token_secret']]);

		if (isset($data[$names['oauth_expires_in']])) {
			$token->setLifeTime($data[$names['oauth_expires_in']]);
			unset($data[$names['oauth_expires_in']]);
		}
		else {
			$token->setLifetime($this->service->getTokenDefaultLifetime());
		}

		$token->setExtraParams($data);

		return $token;
	}
}