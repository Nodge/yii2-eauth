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

namespace nodge\eauth\services;

use nodge\eauth\oauth1\Service;

/**
 * LinkedIn provider class.
 *
 * @package application.extensions.eauth.services
 */
class LinkedinOAuth1Service extends Service
{

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

	protected function fetchAttributes()
	{
		$info = $this->makeSignedRequest('people/~:(id,first-name,last-name,public-profile-url)', array(
			'query' => array(
				'format' => 'json',
			),
		));

		$this->attributes['id'] = $info['id'];
		$this->attributes['name'] = $info['firstName'] . ' ' . $info['lastName'];
		$this->attributes['url'] = $info['publicProfileUrl'];

		return true;
	}

	/**
	 * Returns the error array.
	 *
	 * @param array $response
	 * @return array the error array with 2 keys: code and message. Should be null if no errors.
	 */
	protected function fetchResponseError($response)
	{
		if (isset($response['error-code'])) {
			return array(
				'code' => $response['error-code'],
				'message' => $response['message'],
			);
		} else if (isset($response['errorCode'])) {
			return array(
				'code' => $response['errorCode'],
				'message' => $response['message'],
			);
		}
		return null;
	}
}