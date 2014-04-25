<?php
/**
 * SessionTokenStorage class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\oauth;

use Yii;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;

/**
 * Stores a token in a PHP session.
 */
class SessionTokenStorage implements TokenStorageInterface
{

	const SESSION_TOKEN_PREFIX = 'eauth-token-';
	const SESSION_STATE_PREFIX = 'eauth-state-';

	/**
	 * @var
	 */
	protected $componentName;

	/**
	 * @param string $componentName
	 */
	public function __construct($componentName = 'session')
	{
		$this->componentName = $componentName;
	}

	/**
	 * @return null|object
	 */
	protected function getSession()
	{
		return Yii::$app->get($this->componentName);
	}

	/**
	 * @param string $service
	 * @return TokenInterface
	 * @throws TokenNotFoundException
	 */
	public function retrieveAccessToken($service)
	{
		if ($this->hasAccessToken($service)) {
			return $this->getSession()->get(self::SESSION_TOKEN_PREFIX . $service);
		}
		throw new TokenNotFoundException('Token not found in session, are you sure you stored it?');
	}

	/**
	 * @param string $service
	 * @param TokenInterface $token
	 * @return TokenInterface
	 */
	public function storeAccessToken($service, TokenInterface $token)
	{
		$this->getSession()->set(self::SESSION_TOKEN_PREFIX . $service, $token);
		return $token;
	}

	/**
	 * @param string $service
	 * @return bool
	 */
	public function hasAccessToken($service)
	{
		return $this->getSession()->has(self::SESSION_TOKEN_PREFIX . $service);
	}

	/**
	 * Delete the users token. Aka, log out.
	 *
	 * @param string $service
	 * @return TokenStorageInterface
	 */
	public function clearToken($service)
	{
		$this->getSession()->remove(self::SESSION_TOKEN_PREFIX . $service);
		return $this;
	}

	/**
	 * Delete *ALL* user tokens.
	 *
	 * @return TokenStorageInterface
	 */
	public function clearAllTokens()
	{
		$session = $this->getSession();
		foreach ($session as $key => $value) {
			if (strpos($key, self::SESSION_TOKEN_PREFIX) === 0) {
				$session->remove($key);
			}
		}
		return $this;
	}

	/**
	 * Store the authorization state related to a given service
	 *
	 * @param string $service
	 * @param string $state
	 * @return TokenStorageInterface
	 */
	public function storeAuthorizationState($service, $state)
	{
		$this->getSession()->set(self::SESSION_STATE_PREFIX . $service, $state);
		return $this;
	}

	/**
	 * Check if an authorization state for a given service exists
	 *
	 * @param string $service
	 * @return bool
	 */
	public function hasAuthorizationState($service)
	{
		return $this->getSession()->has(self::SESSION_STATE_PREFIX . $service);
	}

	/**
	 * Retrieve the authorization state for a given service
	 *
	 * @param string $service
	 * @return string
	 * @throws AuthorizationStateNotFoundException
	 */
	public function retrieveAuthorizationState($service)
	{
		if ($this->hasAuthorizationState($service)) {
			return $this->getSession()->get(self::SESSION_STATE_PREFIX . $service);
		}
		throw new AuthorizationStateNotFoundException('State not found in session, are you sure you stored it?');
	}

	/**
	 * Clear the authorization state of a given service
	 *
	 * @param string $service
	 * @return TokenStorageInterface
	 */
	public function clearAuthorizationState($service)
	{
		$this->getSession()->remove(self::SESSION_STATE_PREFIX . $service);
		return $this;
	}

	/**
	 * Delete *ALL* user authorization states. Use with care. Most of the time you will likely
	 * want to use clearAuthorizationState() instead.
	 *
	 * @return TokenStorageInterface
	 */
	public function clearAllAuthorizationStates()
	{
		$session = $this->getSession();
		foreach ($session as $key => $value) {
			if (strpos($key, self::SESSION_STATE_PREFIX) === 0) {
				$session->remove($key);
			}
		}
		return $this;
	}

}
