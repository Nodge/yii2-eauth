<?php
/**
 * Extension class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth;

use Yii;

/**
 * This is the bootstrap class for the yii2-eauth extension.
 */
class Extension extends \yii\base\Extension {
	/**
	 * @inheritdoc
	 */
	public static function init() {
		Yii::setAlias('@eauth', __DIR__);
	}
}