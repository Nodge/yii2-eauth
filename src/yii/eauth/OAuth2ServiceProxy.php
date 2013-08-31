<?php
/**
 * OAuth2ServiceProxy class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace yii\eauth;

use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Token\StdOAuth2Token;

class OAuth2ServiceProxy extends AbstractService {

	/**
	 * @var OAuth2Service the currently used service class
	 */
	protected $service;

	/**
	 * @param OAuth2Service $service
	 */
	public function setService(OAuth2Service $service) {
		$this->service = $service;
	}

	/**
	 * @return string
	 */
	protected function service() {
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
		return new Uri($this->service->getAuthorizationEndpoint());
	}

	/**
	 * @return UriInterface
	 */
	public function getAccessTokenEndpoint() {
		return new Uri($this->service->getAccessTokenEndpoint());
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
		$token->setLifeTime($data[$names['expires_in']]);

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