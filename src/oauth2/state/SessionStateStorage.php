<?php
/**
 * SessionStateStorage class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\oauth2\state;

use \Yii;

/**
 * Base token implementation for any StateStorage version.
 */
class SessionStateStorage extends AbstractStateStorage {

	const SESSION_PREFIX = 'eauth-state-';

	/**
	 * @var int
	 */
	protected $stateLifetime = 3600;

	/**
	 * @param string $id
	 */
	protected function save($id) {
		Yii::$app->getSession()->set(self::SESSION_PREFIX.$id, time());
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	protected function has($id) {
		$time = Yii::$app->getSession()->get(self::SESSION_PREFIX.$id);
		return isset($id) && ($time + $this->stateLifetime > time());
	}

	/**
	 * @param string $id
	 */
	protected function remove($id) {
		Yii::$app->getSession()->remove(self::SESSION_PREFIX.$id);
	}

}
