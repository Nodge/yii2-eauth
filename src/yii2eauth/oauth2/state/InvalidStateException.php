<?php
/**
 * StateStorageInterface interface file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace yii2eauth\oauth2\state;

use yii2eauth\ErrorException;

/**
 * Exception thrown when a state id is not validated.
 */
class InvalidStateException extends ErrorException {

}
