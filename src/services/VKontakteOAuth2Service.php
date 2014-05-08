<?php
/**
 * VKontakteOAuth2Service class file.
 *
 * Register application: http://vk.com/editapp?act=create&site=1
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use nodge\eauth\oauth2\Service;
use OAuth\OAuth2\Service\ServiceInterface;

/**
 * VKontakte provider class.
 *
 * @package application.extensions.eauth.services
 */
class VKontakteOAuth2Service extends Service
{

	const SCOPE_FRIENDS = 'friends';

	protected $name = 'vkontakte';
	protected $title = 'VK.com';
	protected $type = 'OAuth2';
	protected $jsArguments = array('popup' => array('width' => 585, 'height' => 350));

	protected $scopes = array(self::SCOPE_FRIENDS);
	protected $providerOptions = array(
		'authorize' => 'http://api.vk.com/oauth/authorize',
		'access_token' => 'https://api.vk.com/oauth/access_token',
	);
	protected $baseApiUrl = 'https://api.vk.com/method/';

	protected function fetchAttributes()
	{
		$tokenData = $this->getAccessTokenData();

		$info = $this->makeSignedRequest('users.get.json', array(
			'query' => array(
				'uids' => $tokenData['params']['user_id'],
				'fields' => 'photo_big', // uid, first_name and last_name is always available
			),
		));

		$info = $info['response'][0];

		$this->attributes['id'] = $info['uid'];
		$this->attributes['name'] = $info['first_name'] . ' ' . $info['last_name'];
		$this->attributes['url'] = 'http://vk.com/id' . $info['uid'];
        $this->attributes['photo'] = $info['photo_big'];

        $this->attributes['access_token'] = $tokenData['access_token'];
        $this->attributes['expires'] = $tokenData['expires'];

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
		if (isset($response['error'])) {
			return array(
				'code' => is_string($response['error']) ? 0 : $response['error']['error_code'],
//				'message' => is_string($response['error']) ? $response['error'] : $response['error']['error_msg'],
//				'message' => is_string($response['error']) ? $response['error'] : $response['error']['error_msg'],
			);
		} else {
			return null;
		}
	}

	/**
	 * @param array $data
	 * @return string|null
	 */
	public function getAccessTokenResponseError($data)
	{
		if (!isset($data['error'])) {
			return null;
		}
		$error = $data['error'];
		if (isset($data['error_description'])) {
			$error .= ': ' . $data['error_description'];
		}
		return $error;
	}

	/**
	 * Returns a class constant from ServiceInterface defining the authorization method used for the API.
	 *
	 * @return int
	 */
	public function getAuthorizationMethod()
	{
		return ServiceInterface::AUTHORIZATION_METHOD_QUERY_STRING;
	}

}
