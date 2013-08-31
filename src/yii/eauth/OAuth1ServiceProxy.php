<?php
/**
 * OAuth1ServiceProxy class file.
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
use OAuth\OAuth1\Service\AbstractService;
use OAuth\OAuth1\Token\StdOAuth1Token;

class OAuth1ServiceProxy extends AbstractService {

	/**
	 * @var OAuth1Service the currently used service class
	 */
	protected $service;

	/**
	 * @param OAuth1Service $service
	 */
	public function setService(OAuth1Service $service) {
		$this->service = $service;
	}

	/**
	 * @return string
	 */
	public function service() {
		// todo: check service is set
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

		$token->setRequestToken( $data['oauth_token'] );
		$token->setRequestTokenSecret( $data['oauth_token_secret'] );
		$token->setAccessToken( $data['oauth_token'] );
		$token->setAccessTokenSecret( $data['oauth_token_secret'] );

		$token->setEndOfLife(StdOAuth1Token::EOL_NEVER_EXPIRES);
		unset( $data['oauth_token'], $data['oauth_token_secret'] );
		$token->setExtraParams( $data );

		return $token;
	}
}