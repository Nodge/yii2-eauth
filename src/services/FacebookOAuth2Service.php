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

namespace nodge\eauth\services\extended;

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
	const SCOPE_EMAIL = 'user_email';
	const SCOPE_USER_BIRTHDAY = 'user_birthday';
	const SCOPE_USER_HOMETOWN = 'user_hometown';
	const SCOPE_USER_LOCATION = 'user_location';
	const SCOPE_USER_PHOTOS = 'user_photos';

	protected $name = 'facebook';
	protected $title = 'Facebook';
	protected $type = 'OAuth2';
	protected $jsArguments = ['popup' => ['width' => 585, 'height' => 290]];

	protected $scopes = [

        self::SCOPE_EMAIL,
        self::SCOPE_USER_BIRTHDAY,
        self::SCOPE_USER_HOMETOWN,
        self::SCOPE_USER_LOCATION,
        self::SCOPE_USER_PHOTOS,
    ];
	protected $providerOptions = [
		'authorize' => 'https://www.facebook.com/dialog/oauth',
		'access_token' => 'https://graph.facebook.com/oauth/access_token',
	];
	protected $baseApiUrl = 'https://graph.facebook.com/v2.5/';

    protected function fetchAttributes()
    {
        $this->attributes = $this->makeSignedRequest('me', [
            'query' => [
                'fields' => join(',', [
                    'id',
                    'name',
                    'link',
                    'email',
                    'verified',
                    'first_name',
                    'last_name',
                    'gender',
                    'birthday',
                    'hometown',
                    'location',
                    'locale',
                    'timezone',
                    'updated_time',
                ])
            ]
        ]);
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
		// Facebook gives us a query string or json
		if ($response[0] === '{') {
			return json_decode($response, true);
		}
		else {
			parse_str($response, $data);
			return $data;
		}
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
			return [
				'code' => $response['error']['code'],
				'message' => $response['error']['message'],
			];
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
		$error = $this->fetchResponseError($data);
		if (!$error) {
			return null;
		}
		return $error['code'].': '.$error['message'];
	}
}
