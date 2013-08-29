<?php
/**
 * EAuth class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace yii\eauth;

use Yii;
use yii\base\Object;
use yii\helpers\Html;

/**
 * The EAuth class provides simple authentication via OpenID and OAuth providers.
 *
 * @package application.extensions.eauth
 */
class EAuth extends Object {

	/**
	 * todo: get/set
	 * @var array Authorization services and their settings.
	 */
	public $services = array();

	/**
	 * todo: get/set
	 * @var boolean Whether to use popup window for the authorization dialog.
	 */
	public $popup = true;

	/**
	 * todo: get/set
	 * @var mixed Cache component name to use. False to disable cache.
	 */
	public $cache = null;

	/**
	 * todo: get/set
	 * @var integer the number of seconds in which the cached value will expire. 0 means never expire.
	 */
	public $cacheExpire = 0;

	/**
	 * todo: get/set
	 * @var string popup redirect view with custom js code
	 */
	protected $redirectWidget = '\\yii\\eauth\\RedirectWidget';

	public function init() {
		parent::init();

		// set default cache on production environments
		if (!isset($this->cache) && YII_ENV_PROD) {
			$this->cache = 'cache';
		}

		Yii::setAlias('@eauth', __DIR__);
	}

	/**
	 * Returns services settings declared in the authorization classes.
	 * For perfomance reasons it uses cache to store settings array.
	 *
	 * @return \stdClass[] services settings.
	 */
	public function getServices() {
		$services = false;
		if (!empty($this->cache) && Yii::$app->hasComponent($this->cache)) {
			/** @var $cache \yii\caching\Cache */
			$cache = Yii::$app->getComponent($this->cache);
			$services = $cache->get('EAuth.services');
		}

		if (false === $services || !is_array($services)) {
			$services = array();
			foreach ($this->services as $service => $options) {
				/** @var $class ServiceBase */
				$class = $this->getIdentity($service);
				$services[$service] = (object)array(
					'id' => $class->getServiceName(),
					'title' => $class->getServiceTitle(),
					'type' => $class->getServiceType(),
					'jsArguments' => $class->getJsArguments(),
				);
			}
			if (isset($cache)) {
				$cache->set('EAuth.services', $services, $this->cacheExpire);
			}
		}
		return $services;
	}

	/**
	 * Returns the settings of the service.
	 *
	 * @param string $service the service name.
	 * @return \stdClass the service settings.
	 * @throws ErrorException
	 */
	protected function getService($service) {
		$service = strtolower($service);
		$services = $this->getServices();
		if (!isset($services[$service])) {
			throw new ErrorException(Yii::t('eauth', 'Undefined service name: {service}.', array('{service}' => $service)), 500);
		}
		return $services[$service];
	}

	/**
	 * Returns the type of the service.
	 *
	 * @param string $service the service name.
	 * @return string the service type.
	 */
	public function getServiceType($service) {
		$service = $this->getService($service);
		return $service->type;
	}

	/**
	 * Returns the service identity class.
	 *
	 * @param string $service the service name.
	 * @return IAuthService the identity class.
	 * @throws ErrorException
	 */
	public function getIdentity($service) {
		$service = strtolower($service);
		if (!isset($this->services[$service])) {
			throw new ErrorException(Yii::t('eauth', 'Undefined service name: {service}.', array('{service}' => $service)), 500);
		}
		$service = $this->services[$service];

		$service['component'] = $this;

		/** @var $identity IAuthService */
		$identity = Yii::createObject($service);
		return $identity;
	}

	/**
	 * Redirects to url. If the authorization dialog opened in the popup window,
	 * it will be closed instead of redirect. Set $jsRedirect=true if you want
	 * to redirect anyway.
	 *
	 * @param mixed $url url to redirect. Can be route or normal url. See {@link CHtml::normalizeUrl}.
	 * @param boolean $jsRedirect whether to use redirect while popup window is used. Defaults to true.
	 * @param array $params
	 */
	public function redirect($url, $jsRedirect = true, $params = array()) {
		/** @var RedirectWidget $widget */
		$widget = Yii::createObject(array(
			'class' => $this->redirectWidget,
			'url' => Html::url($url),
			'redirect' => $jsRedirect,
			'params' => $params
		));
		$widget->run();
		Yii::$app->getResponse()->send();
	}

	/**
	 * Serialize the identity class.
	 *
	 * @param ServiceBase $identity the class instance.
	 * @return string serialized value.
	 */
	public function toString($identity) {
		return serialize($identity);
	}

	/**
	 * Serialize the identity class.
	 *
	 * @param string $identity serialized value.
	 * @return ServiceBase the class instance.
	 */
	public function fromString($identity) {
		return unserialize($identity);
	}
}
