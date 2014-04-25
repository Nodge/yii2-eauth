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
use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * This is the bootstrap class for the yii2-eauth extension.
 */
class Bootstrap implements BootstrapInterface
{
	/**
	 * @inheritdoc
	 */
	public function bootstrap($app)
	{
		Yii::setAlias('@eauth', __DIR__);
	}
}