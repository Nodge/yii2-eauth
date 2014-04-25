<?php
/**
 * YandexOAuth2Service class file.
 *
 * Register application: https://oauth.yandex.ru/client/my
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use OAuth\Common\Token\TokenInterface;
use nodge\eauth\oauth2\Service;

/**
 * Yandex OAuth provider class.
 *
 * @package application.extensions.eauth.services
 */
class YandexOAuth2Service extends Service
{

	protected $name = 'yandex_oauth';
	protected $title = 'Yandex';
	protected $type = 'OAuth2';
	protected $jsArguments = array('popup' => array('width' => 500, 'height' => 450));
	protected $tokenDefaultLifetime = TokenInterface::EOL_NEVER_EXPIRES;

	protected $scope = array();
	protected $providerOptions = array(
		'authorize' => 'https://oauth.yandex.ru/authorize',
		'access_token' => 'https://oauth.yandex.ru/token',
	);

	protected function fetchAttributes()
	{
		$info = $this->makeSignedRequest('https://login.yandex.ru/info');

		$this->attributes['id'] = $info['id'];
		$this->attributes['name'] = $info['real_name'];
		//$this->attributes['login'] = $info['display_name'];
		//$this->attributes['email'] = $info['emails'][0];
		//$this->attributes['email'] = $info['default_email'];
		$this->attributes['gender'] = ($info['sex'] == 'male') ? 'M' : 'F';

		return true;
	}

}