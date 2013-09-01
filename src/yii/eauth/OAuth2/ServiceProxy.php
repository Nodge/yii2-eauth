<?php
/**
 * OAuth2 ServiceProxy class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace yii\eauth\oauth2;

use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Token\StdOAuth2Token;
use yii\eauth\ErrorException;
use yii\eauth\oauth2\state\InvalidStateException;
use yii\eauth\oauth2\state\StateStorageInterface;

class ServiceProxy extends AbstractService {

	/**
	 * @var Service the currently used service class
	 */
	protected $service;

	/**
	 * @var StateStorageInterface
	 */
	protected $state;

	/**
	 * @param Service $service
	 */
	public function setService(Service $service) {
		$this->service = $service;
	}

	/**
	 * @param StateStorageInterface $storage
	 */
	public function setStateStorage(StateStorageInterface $storage) {
		$this->state = $storage;
	}

	/**
	 * @return string
	 */
	public function service() {
		// todo: check service is set
		return $this->service->getServiceName();
	}

	/**
	 * @return bool
	 */
	public function hasValidAccessToken() {
		$serviceName = $this->service();

		if (!$this->storage->hasAccessToken($serviceName)) {
			return false;
		}

		// todo: check refresh token

		/** @var $token StdOAuth2Token */
		$token = $this->storage->retrieveAccessToken($serviceName);
		return $token->getEndOfLife() > time();
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
	public function getAuthorizationEndpoint() {
		$url = new Uri($this->service->getAuthorizationEndpoint());
		$url->addToQuery('state', $this->state->generateId());
		return $url;
	}

	/**
	 * @return UriInterface
	 */
	public function getAccessTokenEndpoint() {
		return new Uri($this->service->getAccessTokenEndpoint());
	}

	/**
	 * Retrieves and stores the OAuth2 access token after a successful authorization.
	 *
	 * @param string $code The access code from the callback.
	 * @return TokenInterface $token
	 * @throws TokenResponseException
	 * @throws ErrorException
	 * @throws InvalidStateException
	 */
	public function requestAccessToken($code) {
		if (!isset($_GET['state']) || !$this->state->validateId($_GET['state'])) {
			throw new InvalidStateException('The valid "state" argument required.');
		}
		return parent::requestAccessToken($code);
	}

	/**
	 * @param string $responseBody
	 * @return StdOAuth2Token
	 * @throws TokenResponseException
	 */
	protected function parseAccessTokenResponse($responseBody) {
		$data = $this->service->parseAccessTokenResponse($responseBody);

		if (!isset($data) || !is_array($data)) {
			throw new TokenResponseException('Unable to parse response.');
		}
		else if (isset($data['error'])) {
			throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
		}

		$token = new StdOAuth2Token();
		$names = $this->service->getAccessTokenArgumentNames();

		$token->setAccessToken($data[$names['access_token']]);

		if (isset($data[$names['expires_in']])) {
			$token->setLifeTime($data[$names['expires_in']]);
		}
		else {
			$token->setLifetime($this->service->getTokenDefaultLifetime());
		}

		if (isset($data[$names['refresh_token']])) {
			$token->setRefreshToken($data[$names['refresh_token']]);
			unset($data[$names['refresh_token']]);
		}

		unset($data[$names['access_token']]);
		unset($data[$names['expires_in']]);
		$token->setExtraParams($data);

		return $token;
	}
}