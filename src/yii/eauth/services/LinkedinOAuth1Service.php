<?php
/**
 * LinkedinOAuthService class file.
 *
 * Register application: https://www.linkedin.com/secure/developer
 * Note: Integration URL should be filled with a valid callback url.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace yii\eauth\services;

use yii\eauth\OAuth1\Service;

/**
 * LinkedIn provider class.
 *
 * @package application.extensions.eauth.services
 */
class LinkedinOAuth1Service extends Service {

	protected $name = 'linkedin';
	protected $title = 'LinkedIn';
	protected $type = 'OAuth1';
	protected $jsArguments = array('popup' => array('width' => 900, 'height' => 550));

	protected $providerOptions = array(
		'request' => 'https://api.linkedin.com/uas/oauth/requestToken',
		'authorize' => 'https://www.linkedin.com/uas/oauth/authenticate', // https://www.linkedin.com/uas/oauth/authorize
		'access' => 'https://api.linkedin.com/uas/oauth/accessToken',
	);
	protected $baseApiUrl = 'http://api.linkedin.com/v1/';

	protected function fetchAttributes() {
		$info = $this->makeSignedRequest('people/~:(id,first-name,last-name,public-profile-url)');

		$this->attributes['id'] = $info['id'];
		$this->attributes['name'] = $info['first-name'] . ' ' . $info['last-name'];
		$this->attributes['url'] = $info['public-profile-url'];

		return true;
	}

	/**
	 * @param string $response
	 * @return array
	 */
	protected function parseResponse($response) {
		/* @var $simplexml \SimpleXMLElement */
		$simplexml = simplexml_load_string($response);
		return $this->xmlToArray($simplexml);
	}

	/**
	 *
	 * @param \SimpleXMLElement $element
	 * @return array
	 */
	protected function xmlToArray($element) {
		$array = (array)$element;
		foreach ($array as $key => $value) {
			if (is_object($value)) {
				$array[$key] = $this->xmlToArray($value);
			}
		}
		return $array;
	}
}