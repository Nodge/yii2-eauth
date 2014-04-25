<?php
/**
 * ServiceBase class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth;

use Yii;
use yii\base\Object;
use yii\helpers\Url;

/**
 * EAuthServiceBase is a base class for providers.
 *
 * @package application.extensions.eauth
 */
abstract class ServiceBase extends Object implements IAuthService
{

	/**
	 * @var string the service name.
	 */
	protected $name;

	/**
	 *
	 * @var string the service title to display in views.
	 */
	protected $title;

	/**
	 * @var string the service type (e.g. OpenID, OAuth).
	 */
	protected $type;

	/**
	 * @var array arguments for the jQuery.eauth() javascript function.
	 */
	protected $jsArguments = array();

	/**
	 * @var array authorization attributes.
	 * @see getAttribute
	 * @see getItem
	 */
	protected $attributes = array();

	/**
	 * @var boolean whether user was successfuly authenticated.
	 * @see getIsAuthenticated
	 */
	protected $authenticated = false;

	/**
	 * @var boolean whether is attributes was fetched.
	 */
	private $fetched = false;

	/**
	 * @var EAuth the {@link EAuth} application component.
	 */
	private $component;

	/**
	 * @var string the redirect url after successful authorization.
	 */
	private $redirectUrl = '';

	/**
	 * @var string the redirect url after unsuccessful authorization (e.g. user canceled).
	 */
	private $cancelUrl = '';

	/**
	 * PHP getter magic method.
	 * This method is overridden so that service attributes can be accessed like properties.
	 *
	 * @param string $name property name.
	 * @return mixed property value.
	 * @see getAttribute
	 */
	public function __get($name)
	{
		if ($this->hasAttribute($name)) {
			return $this->getAttribute($name);
		} else {
			return parent::__get($name);
		}
	}

	/**
	 * Checks if a attribute value is null.
	 * This method overrides the parent implementation by checking
	 * if the attribute is null or not.
	 *
	 * @param string $name the attribute name.
	 * @return boolean whether the attribute value is null.
	 */
	public function __isset($name)
	{
		if ($this->hasAttribute($name)) {
			return true;
		} else {
			return parent::__isset($name);
		}
	}

	/**
	 * Initialize the component.
	 * Sets the default {@link redirectUrl} and {@link cancelUrl}.
	 */
	public function init()
	{
		parent::init();

		$this->setRedirectUrl(Yii::$app->getUser()->getReturnUrl());

		$service = Yii::$app->getRequest()->getQueryParam('service');
		$cancelUrl = Url::to(['', 'service' => $service], true);

		$this->setCancelUrl($cancelUrl);
	}

	/**
	 * Returns service name(id).
	 *
	 * @return string the service name(id).
	 */
	public function getServiceName()
	{
		return $this->name;
	}

	/**
	 * Returns service title.
	 *
	 * @return string the service title.
	 */
	public function getServiceTitle()
	{
		return Yii::t('eauth', $this->title);
	}

	/**
	 * Returns service type (e.g. OpenID, OAuth).
	 *
	 * @return string the service type (e.g. OpenID, OAuth).
	 */
	public function getServiceType()
	{
		return $this->type;
	}

	/**
	 * Returns arguments for the jQuery.eauth() javascript function.
	 *
	 * @return array the arguments for the jQuery.eauth() javascript function.
	 */
	public function getJsArguments()
	{
		return $this->jsArguments;
	}

	/**
	 * Sets {@link EAuth} application component
	 *
	 * @param EAuth $component the application auth component.
	 */
	public function setComponent($component)
	{
		$this->component = $component;
	}

	/**
	 * Returns the {@link EAuth} application component.
	 *
	 * @return EAuth the {@link EAuth} application component.
	 */
	public function getComponent()
	{
		return $this->component;
	}

	/**
	 * Sets redirect url after successful authorization.
	 *
	 * @param string $url to redirect.
	 */
	public function setRedirectUrl($url)
	{
		$this->redirectUrl = $url;
	}

	/**
	 * @return string the redirect url after successful authorization.
	 */
	public function getRedirectUrl()
	{
		return $this->redirectUrl;
	}

	/**
	 * Sets redirect url after unsuccessful authorization (e.g. user canceled).
	 *
	 * @param string $url
	 */
	public function setCancelUrl($url)
	{
		$this->cancelUrl = $url;
	}

	/**
	 * @return string the redirect url after unsuccessful authorization (e.g. user canceled).
	 */
	public function getCancelUrl()
	{
		return $this->cancelUrl;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Authenticate the user.
	 *
	 * @return boolean whether user was successfuly authenticated.
	 */
	public function authenticate()
	{
		return $this->getIsAuthenticated();
	}

	/**
	 * Whether user was successfuly authenticated.
	 *
	 * @return boolean whether user was successfuly authenticated.
	 */
	public function getIsAuthenticated()
	{
		return $this->authenticated;
	}

	/**
	 * Redirect to the url. If url is null, {@link redirectUrl} will be used.
	 *
	 * @param string $url url to redirect.
	 * @param array $params
	 */
	public function redirect($url = null, $params = array())
	{
		$this->component->redirect(isset($url) ? $url : $this->redirectUrl, true, $params);
	}

	/**
	 * Redirect to the {@link cancelUrl} or simply close the popup window.
	 */
	public function cancel($url = null)
	{
		$this->component->redirect(isset($url) ? $url : $this->cancelUrl, !$this->component->popup);
	}

	/**
	 * Fetch attributes array.
	 *
	 * @return boolean whether the attributes was successfully fetched.
	 */
	protected function fetchAttributes()
	{
		return true;
	}

	/**
	 * Fetch attributes array.
	 * This function is internally used to handle fetched state.
	 */
	protected function _fetchAttributes()
	{
		if (!$this->fetched) {
			$this->fetched = true;
			$result = $this->fetchAttributes();
			if (isset($result)) {
				$this->fetched = $result;
			}
		}
	}

	/**
	 * Returns the user unique id.
	 *
	 * @return mixed the user id.
	 */
	public function getId()
	{
		$this->_fetchAttributes();
		return $this->attributes['id'];
	}

	/**
	 * Returns the array that contains all available authorization attributes.
	 *
	 * @return array the attributes.
	 */
	public function getAttributes()
	{
		$this->_fetchAttributes();
		$attributes = array();
		foreach ($this->attributes as $key => $val) {
			$attributes[$key] = $this->getAttribute($key);
		}
		return $attributes;
	}

	/**
	 * Returns the authorization attribute value.
	 *
	 * @param string $key the attribute name.
	 * @param mixed $default the default value.
	 * @return mixed the attribute value.
	 */
	public function getAttribute($key, $default = null)
	{
		$this->_fetchAttributes();
		$getter = 'get' . $key;
		if (method_exists($this, $getter)) {
			return $this->$getter();
		} else {
			return isset($this->attributes[$key]) ? $this->attributes[$key] : $default;
		}
	}

	/**
	 * Whether the authorization attribute exists.
	 *
	 * @param string $key the attribute name.
	 * @return boolean true if attribute exists, false otherwise.
	 */
	public function hasAttribute($key)
	{
		$this->_fetchAttributes();
		return isset($this->attributes[$key]);
	}

	/**
	 * @return bool
	 */
	public function getIsInsidePopup()
	{
		return isset($_GET['js']);
	}
}
