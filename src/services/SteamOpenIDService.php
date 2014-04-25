<?php
/**
 * SteamOpenIDService class file.
 *
 * @author Dmitry Ananichev <a@qozz.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use nodge\eauth\openid\Service;

/**
 * Steam provider class.
 *
 * @package application.extensions.eauth.services
 */
class SteamOpenIDService extends Service
{

	protected $name = 'steam';
	protected $title = 'Steam';
	protected $type = 'OpenID';
	protected $jsArguments = array('popup' => array('width' => 990, 'height' => 615));

	protected $url = 'http://steamcommunity.com/openid/';

	protected function fetchAttributes()
	{
		if (isset($this->attributes['id'])) {
			$urlChunks = explode('/', $this->attributes['id']);
			if ($count = count($urlChunks)) {
				$name = $urlChunks[$count - 1];
				$this->attributes['name'] = $name;
			}
		}
	}

}