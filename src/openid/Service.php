<?php
/**
 * OpenID Service class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\openid;

use \Yii;
use \LightOpenID;
use yii\web\HttpException;
use nodge\eauth\ServiceBase;
use nodge\eauth\IAuthService;
use nodge\eauth\ErrorException;

/**
 * EOpenIDService is a base class for all OpenID providers.
 *
 * @package application.extensions.eauth
 */
abstract class Service extends ServiceBase implements IAuthService
{

	/**
	 * @var string a pattern that represents the part of URL-space for which an OpenID Authentication request is valid.
	 * See the spec for more info: http://openid.net/specs/openid-authentication-2_0.html#realms
	 * Note: a pattern can be without http(s):// part
	 */
	public $realm;

	/**
	 * @var LightOpenID the openid library instance.
	 */
	private $auth;

	/**
	 * @var string the OpenID authorization url.
	 */
	protected $url;

	/**
	 * @var array the OpenID required attributes.
	 */
	protected $requiredAttributes = array();

	/**
	 * @var array the OpenID optional attributes.
	 */
	protected $optionalAttributes = array();


	/**
	 * Initialize the component.
	 */
	public function init()
	{
		parent::init();
		$this->auth = new LightOpenID(Yii::$app->getRequest()->getHostInfo());
	}

	/**
	 * Authenticate the user.
	 *
	 * @return boolean whether user was successfuly authenticated.
	 * @throws ErrorException
	 * @throws HttpException
	 */
	public function authenticate()
	{
		if (!empty($_REQUEST['openid_mode'])) {
			switch ($_REQUEST['openid_mode']) {
				case 'id_res':
					$this->id_res();
					return true;
					break;

				case 'cancel':
					$this->cancel();
					break;

				default:
					throw new HttpException(400, Yii::t('yii', 'Your request is invalid.'));
					break;
			}
		} else {
			$this->request();
		}

		return false;
	}

	/**
	 * @throws ErrorException
	 */
	protected function id_res()
	{
		try {
			if ($this->auth->validate()) {
				$this->attributes['id'] = $this->auth->identity;
				$this->loadRequiredAttributes();
				$this->loadOptionalAttributes();
				$this->authenticated = true;
			} else {
				throw new ErrorException(Yii::t('eauth', 'Unable to complete the authentication because the required data was not received.', array('provider' => $this->getServiceTitle())));
			}
		} catch (\Exception $e) {
			throw new ErrorException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * @throws ErrorException
	 */
	protected function loadOptionalAttributes()
	{
		$attributes = $this->auth->getAttributes();
		foreach ($this->optionalAttributes as $key => $attr) {
			if (isset($attributes[$attr[1]])) {
				$this->attributes[$key] = $attributes[$attr[1]];
			}
		}
	}

	/**
	 *
	 */
	protected function loadRequiredAttributes()
	{
		$attributes = $this->auth->getAttributes();
		foreach ($this->requiredAttributes as $key => $attr) {
			if (isset($attributes[$attr[1]])) {
				$this->attributes[$key] = $attributes[$attr[1]];
			} else {
				throw new ErrorException(Yii::t('eauth', 'Unable to complete the authentication because the required data was not received.', array('provider' => $this->getServiceTitle())));
			}
		}
	}

	/**
	 * @throws ErrorException
	 */
	protected function request()
	{
		$this->auth->identity = $this->url; //Setting identifier

		$this->auth->required = array(); //Try to get info from openid provider
		foreach ($this->requiredAttributes as $attribute) {
			$this->auth->required[$attribute[0]] = $attribute[1];
		}
		foreach ($this->optionalAttributes as $attribute) {
			$this->auth->required[$attribute[0]] = $attribute[1];
		}

		$this->auth->realm = $this->getRealm();
		$this->auth->returnUrl = Yii::$app->getRequest()->getHostInfo() . Yii::$app->getRequest()->getUrl(); //getting return URL

		try {
			$url = $this->auth->authUrl();
			Yii::$app->getResponse()->redirect($url)->send();
		} catch (\Exception $e) {
			throw new ErrorException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * @return string
	 */
	protected function getRealm()
	{
		if (isset($this->realm)) {
			if (!preg_match('#^[a-z]+\://#', $this->realm)) {
				return 'http' . (Yii::$app->getRequest()->getIsSecureConnection() ? 's' : '') . '://' . $this->realm;
			} else {
				return $this->realm;
			}
		} else {
			return Yii::$app->getRequest()->getHostInfo();
		}
	}
}
