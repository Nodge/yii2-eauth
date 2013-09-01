<?php
/**
 * AbstractStateStorage class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace yii\eauth\oauth2\state;

use yii\helpers\Security;

/**
 * Base token implementation for any StateStorage version.
 */
abstract class AbstractStateStorage implements StateStorageInterface {

	/**
	 * @return string
	 */
	public function generateId() {
		$id = Security::generateRandomKey();
		$this->save($id);
		return $id;
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function validateId($id) {
		if ($this->has($id)) {
			$this->remove($id);
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Saved generated id in storage.
	 * @param string $id
	 */
	abstract protected function save($id);

	/**
	 * Looks up for id.
	 * @param string $id
	 * @return bool
	 */
	abstract protected function has($id);

	/**
	 * Removes id from storage.
	 * @param string $id
	 */
	abstract protected function remove($id);

}
