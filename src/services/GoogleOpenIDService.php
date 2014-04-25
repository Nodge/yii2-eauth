<?php
/**
 * GoogleOpenIDService class file.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use nodge\eauth\openid\Service;

/**
 * Google provider class.
 *
 * @package application.extensions.eauth.services
 */
class GoogleOpenIDService extends Service
{

	protected $name = 'google';
	protected $title = 'Google';
	protected $type = 'OpenID';
	protected $jsArguments = array('popup' => array('width' => 880, 'height' => 520));

	protected $url = 'https://www.google.com/accounts/o8/id';
	protected $requiredAttributes = array(
		'name' => array('firstname', 'namePerson/first'),
		//'email' => array('email', 'contact/email'),
	);
	protected $optionalAttributes = array(
		//'lastname' => array('lastname', 'namePerson/last'),
		//'language' => array('language', 'pref/language'),
	);

	/*protected function fetchAttributes() {
		$this->attributes['fullname'] = $this->attributes['name'].' '.$this->attributes['lastname'];
		return true;
	}*/
}