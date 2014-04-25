<?php
/**
 * ControllerBehavior class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\openid;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;

/**
 * @package application.extensions.eauth
 */
class ControllerBehavior extends ActionFilter
{
	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 *
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		$request = Yii::$app->getRequest();

		if (in_array($request->getBodyParam('openid_mode', ''), ['id_res', 'cancel'])) {
			$request->enableCsrfValidation = false;
		}

		return parent::beforeAction($action);
	}
}
