<?php
/**
 * EAuth class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * The EAuth class provides simple authentication via OpenID and OAuth providers.
 *
 * @package application.extensions.eauth
 */
class EAuth extends Object
{

	/**
	 * @var array Authorization services and their settings.
	 */
	protected $services = array();

	/**
	 * @var boolean Whether to use popup window for the authorization dialog.
	 */
	protected $popup = true;

	/**
	 * @var string|bool Cache component name to use. False to disable cache.
	 */
	public $cache = null;

	/**
	 * @var integer the number of seconds in which the cached value will expire. 0 means never expire.
	 */
	public $cacheExpire = 0;

	/**
	 * @var string popup redirect view with custom js code
	 */
	protected $redirectWidget = '\\nodge\\eauth\\RedirectWidget';

	/**
	 * @var array TokenStorage class.
	 */
	protected $tokenStorage = array(
		'class' => 'nodge\eauth\oauth\SessionTokenStorage',
	);

	/**
	 * @var array HttpClient class.
	 */
	protected $httpClient = array(
		'class' => 'nodge\eauth\oauth\HttpClient',
//		'useStreamsFallback' => false,
	);

	/**
	 * Initialize the component.
	 */
	public function init()
	{
		parent::init();

		// set default cache on production environments
		if (!isset($this->cache) && YII_ENV_PROD) {
			$this->cache = 'cache';
		}
	}

	/**
	 * @param array $services
	 */
	public function setServices($services)
	{
		$this->services = $services;
	}

	/**
	 * Returns services settings declared in the authorization classes.
	 * For perfomance reasons it uses cache to store settings array.
	 *
	 * @return \stdClass[] services settings.
	 */
	public function getServices()
	{
		$services = false;
		if (!empty($this->cache) && Yii::$app->has($this->cache)) {
			/** @var $cache \yii\caching\Cache */
			$cache = Yii::$app->get($this->cache);
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
	 * @param bool $usePopup
	 */
	public function setPopup($usePopup)
	{
		$this->popup = $usePopup;
	}

	/**
	 * @return bool
	 */
	public function getPopup()
	{
		return $this->popup;
	}

	/**
	 * @param string|bool $cache
	 */
	public function setCache($cache)
	{
		$this->cache = $cache;
	}

	/**
	 * @return string|bool
	 */
	public function getCache()
	{
		return $this->cache;
	}

	/**
	 * @param int $cacheExpire
	 */
	public function setCacheExpire($cacheExpire)
	{
		$this->cacheExpire = $cacheExpire;
	}

	/**
	 * @return int
	 */
	public function getCacheExpire()
	{
		return $this->cacheExpire;
	}

	/**
	 * @param string $redirectWidget
	 */
	public function setRedirectWidget($redirectWidget)
	{
		$this->redirectWidget = $redirectWidget;
	}

	/**
	 * @return string
	 */
	public function getRedirectWidget()
	{
		return $this->redirectWidget;
	}

	/**
	 * @param array $config
	 */
	public function setTokenStorage(array $config)
	{
		$this->tokenStorage = ArrayHelper::merge($this->tokenStorage, $config);
	}

	/**
	 * @return array
	 */
	public function getTokenStorage()
	{
		return $this->tokenStorage;
	}

	/**
	 * @param array $config
	 */
	public function setHttpClient(array $config)
	{
		$this->httpClient = ArrayHelper::merge($this->httpClient, $config);
	}

	/**
	 * @return array
	 */
	public function getHttpClient()
	{
		return $this->httpClient;
	}

	/**
	 * Returns the settings of the service.
	 *
	 * @param string $service the service name.
	 * @return \stdClass the service settings.
	 * @throws ErrorException
	 */
	protected function getService($service)
	{
		$service = strtolower($service);
		$services = $this->getServices();
		if (!isset($services[$service])) {
			throw new ErrorException(Yii::t('eauth', 'Undefined service name: {service}.', array('service' => $service)), 500);
		}
		return $services[$service];
	}

	/**
	 * Returns the type of the service.
	 *
	 * @param string $service the service name.
	 * @return string the service type.
	 */
	public function getServiceType($service)
	{
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
	public function getIdentity($service)
	{
		$service = strtolower($service);
		if (!isset($this->services[$service])) {
			throw new ErrorException(Yii::t('eauth', 'Undefined service name: {service}.', array('service' => $service)), 500);
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
	public function redirect($url, $jsRedirect = true, $params = array())
	{
		/** @var RedirectWidget $widget */
		$widget = Yii::createObject(array(
			'class' => $this->redirectWidget,
			'url' => Url::to($url),
			'redirect' => $jsRedirect,
			'params' => $params
		));
		ob_start();
		$widget->run();
		$output = ob_get_clean();
		$response = Yii::$app->getResponse();
		$response->content = $output;
		$response->send();
		exit();
	}

	/**
	 * Serialize the identity class.
	 *
	 * @param ServiceBase $identity the class instance.
	 * @return string serialized value.
	 */
	public function toString($identity)
	{
		return serialize($identity);
	}

	/**
	 * Serialize the identity class.
	 *
	 * @param string $identity serialized value.
	 * @return ServiceBase the class instance.
	 */
	public function fromString($identity)
	{
		return unserialize($identity);
	}

}
