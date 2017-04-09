<?php
/**
 * Extension class file.
 *
 * @author Pyrotech Solutions <developers@pyrotechsolutions.com>
 * @link http://github.com/PyrotechSolutions/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace ptech\eauth;

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