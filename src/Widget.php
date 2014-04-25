<?php
/**
 * Widget class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth;

use Yii;

/**
 * The EAuthWidget widget prints buttons to authenticate user with OpenID and OAuth providers.
 *
 * @package application.extensions.eauth
 */
class Widget extends \yii\base\Widget
{

	/**
	 * @var string EAuth component name.
	 */
	public $component = 'eauth';

	/**
	 * @var array the services.
	 * @see EAuth::getServices()
	 */
	public $services = null;

	/**
	 * @var array predefined services. If null then use all services. Default is null.
	 */
	public $predefinedServices = null;

	/**
	 * @var boolean whether to use popup window for authorization dialog. Javascript required.
	 */
	public $popup = null;

	/**
	 * @var string the action to use for dialog destination. Default: the current route.
	 */
	public $action = null;

	/**
	 * @var boolean include the CSS file. Default is true.
	 * If this is set false, you are responsible to explicitly include the necessary CSS file in your page.
	 */
	public $assetBundle = 'nodge\\eauth\\assets\\WidgetAssetBundle';

	/**
	 * Initializes the widget.
	 * This method is called by {@link CBaseController::createWidget}
	 * and {@link CBaseController::beginWidget} after the widget's
	 * properties have been initialized.
	 */
	public function init()
	{
		parent::init();

		// EAuth component
		/** @var $component \nodge\eauth\EAuth */
		$component = Yii::$app->get($this->component);

		// Some default properties from component configuration
		if (!isset($this->services)) {
			$this->services = $component->getServices();
		}

		if (is_array($this->predefinedServices)) {
			$_services = array();
			foreach ($this->predefinedServices as $_serviceName) {
				if (isset($this->services[$_serviceName])) {
					$_services[$_serviceName] = $this->services[$_serviceName];
				}
			}
			$this->services = $_services;
		}

		if (!isset($this->popup)) {
			$this->popup = $component->popup;
		}

		// Set the current route, if it is not set.
		if (!isset($this->action)) {
			$this->action = '/' . Yii::$app->requestedRoute;
		}
	}

	/**
	 * Executes the widget.
	 * This method is called by {@link CBaseController::endWidget}.
	 */
	public function run()
	{
		parent::run();
		echo $this->render('widget', array(
			'id' => $this->getId(),
			'services' => $this->services,
			'action' => $this->action,
			'popup' => $this->popup,
			'assetBundle' => $this->assetBundle,
		));
	}
}
