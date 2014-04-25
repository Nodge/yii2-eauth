<?php
/**
 * OAuth2 ServiceProxy class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\oauth2;

use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\AbstractService;
use OAuth\OAuth2\Token\StdOAuth2Token;

class ServiceProxy extends AbstractService
{

	/**
	 * @var Service the currently used service class
	 */
	protected $service;

	/**
	 * @param CredentialsInterface $credentials
	 * @param ClientInterface $httpClient
	 * @param TokenStorageInterface $storage
	 * @param array $scopes
	 * @param UriInterface $baseApiUri
	 * @param Service $service
	 */
	public function __construct(
		CredentialsInterface $credentials,
		ClientInterface $httpClient,
		TokenStorageInterface $storage,
		$scopes = array(),
		UriInterface $baseApiUri = null,
		Service $service
	)
	{
		$this->service = $service;
		parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri, $service->getValidateState());
	}

	/**
	 * @return string
	 */
	public function service()
	{
		return $this->service->getServiceName();
	}

	/**
	 * Validate scope
	 *
	 * @param string $scope
	 * @return bool
	 */
	public function isValidScope($scope)
	{
		$reflectionClass = new \ReflectionClass(get_class($this->service));
		return in_array($scope, $reflectionClass->getConstants(), true);
	}

	/**
	 * @return bool
	 */
	public function hasValidAccessToken()
	{
		$serviceName = $this->service();

		if (!$this->storage->hasAccessToken($serviceName)) {
			return false;
		}

		/** @var $token StdOAuth2Token */
		$token = $this->storage->retrieveAccessToken($serviceName);
		$valid = $this->checkTokenLifetime($token);

		if (!$valid) {
			$refreshToken = $token->getRefreshToken();
			if (isset($refreshToken)) {
				$token = $this->refreshAccessToken($token);
				return $this->checkTokenLifetime($token);
			}
		}

		return $valid;
	}

	/**
	 * @param TokenInterface $token
	 * @return bool
	 */
	protected function checkTokenLifetime($token)
	{
		// assume that we have at least a minute to execute a queries.
		return $token->getEndOfLife() - 60 > time()
		|| $token->getEndOfLife() === TokenInterface::EOL_NEVER_EXPIRES;
	}

	/**
	 * @return null|TokenInterface
	 */
	public function getAccessToken()
	{
		if (!$this->hasValidAccessToken()) {
			return null;
		}

		$serviceName = $this->service();
		return $this->storage->retrieveAccessToken($serviceName);
	}

	/**
	 * @return UriInterface
	 */
	public function getAuthorizationEndpoint()
	{
		return new Uri($this->service->getAuthorizationEndpoint());
	}

	/**
	 * @return UriInterface
	 */
	public function getAccessTokenEndpoint()
	{
		return new Uri($this->service->getAccessTokenEndpoint());
	}

	/**
	 * @param string $responseBody
	 * @return StdOAuth2Token
	 * @throws TokenResponseException
	 */
	protected function parseAccessTokenResponse($responseBody)
	{
		$data = $this->service->parseAccessTokenResponse($responseBody);

		if (!isset($data) || !is_array($data)) {
			throw new TokenResponseException('Unable to parse response.');
		}

		$error = $this->service->getAccessTokenResponseError($data);
		if (isset($error)) {
			throw new TokenResponseException('Error in retrieving token: "' . $error . '"');
		}

		$token = new StdOAuth2Token();
		$names = $this->service->getAccessTokenArgumentNames();

		$token->setAccessToken($data[$names['access_token']]);
		unset($data[$names['access_token']]);

		if (isset($data[$names['expires_in']])) {
			$token->setLifeTime($data[$names['expires_in']]);
			unset($data[$names['expires_in']]);
		} else {
			$token->setLifetime($this->service->getTokenDefaultLifetime());
		}

		if (isset($data[$names['refresh_token']])) {
			$token->setRefreshToken($data[$names['refresh_token']]);
			unset($data[$names['refresh_token']]);
		}

		$token->setExtraParams($data);

		return $token;
	}

	/**
	 * Return any additional headers always needed for this service implementation's OAuth calls.
	 *
	 * @return array
	 */
	protected function getExtraOAuthHeaders()
	{
		return $this->service->getExtraOAuthHeaders();
	}

	/**
	 * Return any additional headers always needed for this service implementation's API calls.
	 *
	 * @return array
	 */
	protected function getExtraApiHeaders()
	{
		return $this->service->getExtraApiHeaders();
	}

	/**
	 * Returns a class constant from ServiceInterface defining the authorization method used for the API
	 * Header is the sane default.
	 *
	 * @return int
	 */
	protected function getAuthorizationMethod()
	{
		return $this->service->getAuthorizationMethod();
	}

	/**
	 * Returns the url to redirect to for authorization purposes.
	 *
	 * @param array $additionalParameters
	 * @return Uri
	 */
	public function getAuthorizationUri(array $additionalParameters = array())
	{
		$parameters = array_merge($additionalParameters, array(
			'type' => 'web_server',
			'client_id' => $this->credentials->getConsumerId(),
			'redirect_uri' => $this->credentials->getCallbackUrl(),
			'response_type' => 'code',
		));

		$parameters['scope'] = implode($this->service->getScopeSeparator(), $this->scopes);

		if ($this->needsStateParameterInAuthUrl()) {
			if (!isset($parameters['state'])) {
				$parameters['state'] = $this->generateAuthorizationState();
			}
			$this->storeAuthorizationState($parameters['state']);
		}

		// Build the url
		$url = clone $this->getAuthorizationEndpoint();
		foreach ($parameters as $key => $val) {
			$url->addToQuery($key, $val);
		}

		return $url;
	}
}
