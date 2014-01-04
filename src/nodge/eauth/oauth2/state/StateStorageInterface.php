<?php
/**
 * StateStorageInterface interface file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\oauth2\state;

/**
 * Base token interface for any OAuth version.
 */
interface StateStorageInterface {

	/**
	 * Generates a random state id.
	 * @return string
	 */
	public function generateId();

	/**
	 * Validates state argument.
	 * @param string $id
	 * @return bool
	 */
	public function validateId($id);

}
