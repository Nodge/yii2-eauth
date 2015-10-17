<?php
/**
 * An example of extending the provider class.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services\extended;

class YandexOpenIDService extends \nodge\eauth\services\YandexOpenIDService
{

	protected $jsArguments = ['popup' => ['width' => 900, 'height' => 620]];

	protected $requiredAttributes = [
		'name' => ['fullname', 'namePerson'],
		'username' => ['nickname', 'namePerson/friendly'],
		'email' => ['email', 'contact/email'],
	];
	protected $optionalAttributes = [
		'gender' => ['gender', 'person/gender'],
		'birthDate' => ['dob', 'birthDate'],
	];

	protected function fetchAttributes()
	{
		if (isset($this->attributes['username']) && !empty($this->attributes['username'])) {
			$this->attributes['url'] = 'http://openid.yandex.ru/' . $this->attributes['username'];
		}

		if (isset($this->attributes['birthDate']) && !empty($this->attributes['birthDate'])) {
			$this->attributes['birthDate'] = strtotime($this->attributes['birthDate']);
		}

		return true;
	}
}