<?php
/**
 * AuthRedirectWidget class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth;

use \Yii;
use yii\helpers\ArrayHelper;

/**
 * The EAuthRedirectWidget widget displays the redirect page after returning from provider.
 *
 * @package application.extensions.eauth
 */
class RedirectWidget extends Widget
{

	/**
	 * @var mixed the widget mode. Default to "login".
	 */
	public $url = null;

	/**
	 * @var boolean whether to use redirect inside the popup window.
	 */
	public $redirect = true;

	/**
	 * @var string
	 */
	public $view = 'redirect';

	/**
	 * @var array
	 */
	public $params = array();

	/**
	 * Executes the widget.
	 */
	public function run()
	{
		echo $this->render($this->view,
			ArrayHelper::merge(array(
				'id' => $this->getId(),
				'url' => $this->url,
				'redirect' => $this->redirect,
			), $this->params)
		);
	}
}
