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

/**
 * Stores a token in a PHP session.
 */
class SessionTokenStorage implements TokenStorageInterface {

	const SESSION_PREFIX = 'eauth-token-';

	/**
	 * @var
	 */
	protected $componentName;

	/**
	 * @param string $componentName
	 */
	public function __construct($componentName = 'session') {
		$this->componentName = $componentName;
	}

	/**
	 * @param string $service
	 * @return TokenInterface
	 * @throws TokenNotFoundException
	 */
	public function retrieveAccessToken($service) {
		if ($this->hasAccessToken($service)) {
			return Yii::$app->getSession()->get(self::SESSION_PREFIX . $service);
		}
		throw new TokenNotFoundException('Token not found in session, are you sure you stored it?');
	}

	/**
	 * @param string $service
	 * @param TokenInterface $token
	 * @return TokenInterface
	 */
	public function storeAccessToken($service, TokenInterface $token) {
		Yii::$app->getSession()->set(self::SESSION_PREFIX . $service, $token);
		return $token;
	}

	/**
	 * @param string $service
	 * @return bool
	 */
	public function hasAccessToken($service) {
		return Yii::$app->getSession()->has(self::SESSION_PREFIX . $service);
	}

	/**
	 * Delete the users token. Aka, log out.
	 *
	 * @param string $service
	 * @return TokenStorageInterface
	 */
	public function clearToken($service) {
		Yii::$app->getSession()->remove(self::SESSION_PREFIX . $service);
		return $this;
	}

	/**
	 * Delete *ALL* user tokens.
	 *
	 * @return TokenStorageInterface
	 */
	public function clearAllTokens() {
		$session = Yii::$app->getSession();
		foreach ($session as $key => $value) {
			if (strpos($key, self::SESSION_PREFIX) === 0) {
				$session->remove($key);
			}
		}
		return $this;
	}

}
