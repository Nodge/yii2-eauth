<?php
/**
 * YahooOpenIDService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use nodge\eauth\openid\Service;

/**
 * Yahoo provider class.
 *
 * @package application.extensions.eauth.services
 */
class YahooOpenIDService extends Service
{

	protected $name = 'yahoo';
	protected $title = 'Yahoo';
	protected $type = 'OpenID';
	protected $jsArguments = ['popup' => ['width' => 880, 'height' => 520]];

	protected $url = 'https://me.yahoo.com';
	protected $requiredAttributes = [
		'name' => ['fullname', 'namePerson'],
//		'login' => ['nickname', 'namePerson/friendly'],
//		'email' => ['email', 'contact/email'],
	];
	protected $optionalAttributes = [
//		'language' => ['language', 'pref/language'],
//		'gender' => ['gender', 'person/gender'],
//		'timezone' => ['timezone', 'pref/timezone'],
//		'image' => ['image', 'media/image/default'],
	];

	/*protected function fetchAttributes() {
		$this->attributes['fullname'] = $this->attributes['name'].' '.$this->attributes['lastname'];
		return true;
	}*/
}