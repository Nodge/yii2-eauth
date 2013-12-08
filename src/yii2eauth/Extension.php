<?php
/**
 * Extension class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace yii2eauth;

use Yii;

/**
 * This is the bootstrap class for the Yii JUI extension.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Extension extends \yii\base\Extension {
	/**
	 * @inheritdoc
	 */
	public static function init() {
		Yii::setAlias('@yii2eauth', __DIR__);
	}
}