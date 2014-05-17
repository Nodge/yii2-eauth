<?php
/**
 * FacebookOAuth2Service class file.
 *
 * Register application: https://developers.facebook.com/apps/
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use nodge\eauth\oauth2\Service;

/**
 * Facebook provider class.
 *
 * @package application.extensions.eauth.services
 */
class FacebookOAuth2Service extends Service
{

	/**
	 * Full list of scopes may be found here:
	 * https://developers.facebook.com/docs/authentication/permissions/
	 */
	const SCOPE_EMAIL = 'email';
	const SCOPE_USER_BIRTHDAY = 'user_birthday';
	const SCOPE_USER_HOMETOWN = 'user_hometown';
	const SCOPE_USER_LOCATION = 'user_location';
	const SCOPE_USER_PHOTOS = 'user_photos';
    const SCOPE_USER_FRIENDS = 'user_friends';

	protected $name = 'facebook';
	protected $title = 'Facebook';
	protected $type = 'OAuth2';
	protected $jsArguments = array('popup' => array('width' => 585, 'height' => 290));

    protected $scopes = array(self::SCOPE_USER_FRIENDS);
	protected $providerOptions = array(
		'authorize' => 'https://www.facebook.com/dialog/oauth',
		'access_token' => 'https://graph.facebook.com/oauth/access_token',
	);
	protected $baseApiUrl = 'https://graph.facebook.com/';

	protected $errorParam = 'error_code';
	protected $errorDescriptionParam = 'error_message';

	protected function fetchAttributes()
	{
        $tokenData = $this->getAccessTokenData();

		$info = $this->makeSignedRequest('me');

		$this->attributes['id'] = $info['id'];
		$this->attributes['name'] = $info['name'];
		$this->attributes['url'] = $info['link'];
        $this->attributes['photo'] = "http://graph.facebook.com/{$info['id']}/picture?type=large";

        $this->attributes['access_token'] = $tokenData['access_token'];
        $this->attributes['expires'] = $tokenData['expires'];

        $friends_data = $this->makeSignedRequest('me/friends');
        $this->attributes['friend_uids'] = [];
        foreach ($friends_data['data'] as $friend) {
            $this->attributes['friend_uids'][] = $friend['id'];
        }

		return true;
	}

	/**
	 * @return array
	 */
	public function getAccessTokenArgumentNames()
	{
		$names = parent::getAccessTokenArgumentNames();
		$names['expires_in'] = 'expires';
		return $names;
	}

	/**
	 * @param string $response
	 * @return array
	 */
	public function parseAccessTokenResponse($response)
	{
		// Facebook gives us a query string...
		parse_str($response, $data);
		return $data;
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
				'code' => $response['error']['code'],
				'message' => $response['error']['message'],
			);
		} else {
			return null;
		}
	}
}
