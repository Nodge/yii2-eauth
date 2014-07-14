<?php
/**
 * OAuth2 Service class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\oauth2;

use Yii;
use yii\helpers\Url;
use OAuth\Common\Exception\Exception as OAuthException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\Credentials;
use OAuth\OAuth2\Service\ServiceInterface;
use nodge\eauth\EAuth;
use nodge\eauth\ErrorException;
use nodge\eauth\IAuthService;
use nodge\eauth\oauth\ServiceBase;

/**
 * EOAuthService is a base class for all OAuth providers.
 *
 * @package application.extensions.eauth
 */
abstract class Service extends ServiceBase implements IAuthService
{

	/**
	 * @var string OAuth2 client id.
	 */
	protected $clientId;

	/**
	 * @var string OAuth2 client secret key.
	 */
	protected $clientSecret;

	/**
	 * @var array OAuth scopes.
	 */
	protected $scopes = array();

	/**
	 * @var string
	 */
	protected $scopeSeparator = ' ';

	/**
	 * @var array Provider options. Must contain the keys: authorize, access_token.
	 */
	protected $providerOptions = array(
		'authorize' => '',
		'access_token' => '',
	);

	/**
	 * @var string Error key name in _GET options.
	 */
	protected $errorParam = 'error';

	/**
	 * @var string Error description key name in _GET options.
	 */
	protected $errorDescriptionParam = 'error_description';

	/**
	 * @var string Error code for access_denied response.
	 */
	protected $errorAccessDeniedCode = 'access_denied';

	/**
	 * @var string The display name for popup window. False to disable display mode.
	 */
	protected $popupDisplayName = 'popup';

	/**
	 * @var bool Whether to use the State param to improve security.
	 */
	protected $validateState = true;

	/**
	 * @var ServiceProxy
	 */
	private $_proxy;

	/**
	 * Initialize the component.
	 *
	 * @param EAuth $component the component instance.
	 * @param array $options properties initialization.
	 */
//	public function init($component, $options = array()) {
//		parent::init($component, $options);
//	}

	/**
	 * @param string $id
	 */
	public function setClientId($id)
	{
		$this->clientId = $id;
	}

	/**
	 * @param string $secret
	 */
	public function setClientSecret($secret)
	{
		$this->clientSecret = $secret;
	}

	/**
	 * @param string|array $scopes
	 */
	public function setScope($scopes)
	{
		if (!is_array($scopes)) {
			$scopes = array($scopes);
		}

		$resolvedScopes = array();
		$reflClass = new \ReflectionClass($this);
		$constants = $reflClass->getConstants();

		foreach ($scopes as $scope) {
			$key = strtoupper('SCOPE_' . $scope);

			// try to find a class constant with this name
			if (array_key_exists($key, $constants)) {
				$resolvedScopes[] = $constants[$key];
			} else {
				$resolvedScopes[] = $scopes;
			}
		}

		$this->scopes = $resolvedScopes;
	}

	/**
	 * @param bool $validate
	 */
	public function setValidateState($validate)
	{
		$this->validateState = $validate;
	}

	/**
	 * @return bool
	 */
	public function getValidateState()
	{
		return $this->validateState;
	}

	/**
	 * @return ServiceProxy
	 */
	protected function getProxy()
	{
		if (!isset($this->_proxy)) {
			$tokenStorage = $this->getTokenStorage();
			$httpClient = $this->getHttpClient();
			$credentials = new Credentials($this->clientId, $this->clientSecret, $this->getCallbackUrl());
			$this->_proxy = new ServiceProxy($credentials, $httpClient, $tokenStorage, $this->scopes, null, $this);
		}
		return $this->_proxy;
	}

	/**
	 * @return string the current url
	 */
	protected function getCallbackUrl()
	{
		if (isset($_GET['redirect_uri'])) {
			$url = $_GET['redirect_uri'];
		}
		else {
			$route = Yii::$app->getRequest()->getQueryParams();
			array_unshift($route, '');

			// Can not use these params in OAuth2 callbacks
			foreach (['code', 'state', 'redirect_uri'] as $param) {
				if (isset($route[$param])) {
					unset($route[$param]);
				}
			}

			$url = Url::to($route, true);
		}

		return $url;
	}

	/**
	 * Authenticate the user.
	 *
	 * @return boolean whether user was successfuly authenticated.
	 * @throws ErrorException
	 */
	public function authenticate()
	{
		if (!$this->checkError()) {
			return false;
		}

		try {
			$proxy = $this->getProxy();

			if (!empty($_GET['code'])) {
				// This was a callback request from a service, get the token
				$proxy->requestAccessToken($_GET['code']);
				$this->authenticated = true;
			} else if ($proxy->hasValidAccessToken()) {
				$this->authenticated = true;
			} else {
				/** @var $url Uri */
				$url = $proxy->getAuthorizationUri();
				Yii::$app->getResponse()->redirect($url->getAbsoluteUri())->send();
			}
		} catch (OAuthException $e) {
			throw new ErrorException($e->getMessage(), $e->getCode(), 1, $e->getFile(), $e->getLine(), $e);
		}

		return $this->getIsAuthenticated();
	}

	/**
	 * Check request params for error code and message.
	 *
	 * @return bool
	 * @throws ErrorException
	 */
	protected function checkError()
	{
		if (isset($_GET[$this->errorParam])) {
			$error_code = $_GET[$this->errorParam];
			if ($error_code === $this->errorAccessDeniedCode) {
				// access_denied error (user canceled)
				$this->cancel();
			} else {
				$error = $error_code;
				if (isset($_GET[$this->errorDescriptionParam])) {
					$error = $_GET[$this->errorDescriptionParam] . ' (' . $error . ')';
				}
				throw new ErrorException($error);
			}
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	public function getAuthorizationEndpoint()
	{
		$url = $this->providerOptions['authorize'];
		if ($this->popupDisplayName !== false && $this->getIsInsidePopup()) {
			$url = new Uri($url);
			$url->addToQuery('display', $this->popupDisplayName);
			$url = $url->getAbsoluteUri();
		}
		return $url;
	}

	/**
	 * @return string
	 */
	public function getAccessTokenEndpoint()
	{
		return $this->providerOptions['access_token'];
	}

	/**
	 * @param string $response
	 * @return array
	 */
	public function parseAccessTokenResponse($response)
	{
		return json_decode($response, true);
	}

	/**
	 * @return array
	 */
	public function getAccessTokenArgumentNames()
	{
		return array(
			'access_token' => 'access_token',
			'expires_in' => 'expires_in',
			'refresh_token' => 'refresh_token',
		);
	}

	/**
	 * Return any additional headers always needed for this service implementation's OAuth calls.
	 *
	 * @return array
	 */
	public function getExtraOAuthHeaders()
	{
		return array();
	}

	/**
	 * Return any additional headers always needed for this service implementation's API calls.
	 *
	 * @return array
	 */
	public function getExtraApiHeaders()
	{
		return array();
	}

	/**
	 * Returns a class constant from ServiceInterface defining the authorization method used for the API
	 * Header is the sane default.
	 *
	 * @return int
	 */
	public function getAuthorizationMethod()
	{
		return ServiceInterface::AUTHORIZATION_METHOD_HEADER_OAUTH;
	}

	/**
	 * @return string
	 */
	public function getScopeSeparator()
	{
		return $this->scopeSeparator;
	}
}