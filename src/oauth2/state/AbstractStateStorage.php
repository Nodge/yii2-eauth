<?php
/**
 * AbstractStateStorage class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\oauth2\state;

/**
 * Base token implementation for any StateStorage version.
 */
abstract class AbstractStateStorage implements StateStorageInterface {

	/**
	 * @return string
	 */
	public function generateId() {
		$id = md5(uniqid('eauth-state'));
		$this->save($id);
		return $id;
	}

	/**
	 * @param string $id
	 * @param bool $remove
	 * @return bool
	 */
	public function validateId($id, $remove = true) {
		if ($this->has($id)) {
			if ($remove) {
				$this->remove($id);
			}
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
