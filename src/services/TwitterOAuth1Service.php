<?php
/**
 * TwitterOAuthService class file.
 *
 * Register application: https://dev.twitter.com/apps/new
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use OAuth\OAuth1\Token\TokenInterface;
use nodge\eauth\oauth1\Service;


/**
 * Twitter provider class.
 *
 * @package application.extensions.eauth.services
 */
class TwitterOAuth1Service extends Service
{

	protected $name = 'twitter';
	protected $title = 'Twitter';
	protected $type = 'OAuth1';
	protected $jsArguments = array('popup' => array('width' => 900, 'height' => 550));

	protected $providerOptions = array(
		'request' => 'https://api.twitter.com/oauth/request_token',
		'authorize' => 'https://api.twitter.com/oauth/authenticate', //https://api.twitter.com/oauth/authorize
		'access' => 'https://api.twitter.com/oauth/access_token',
	);
	protected $baseApiUrl = 'https://api.twitter.com/1.1/';
	protected $tokenDefaultLifetime = TokenInterface::EOL_NEVER_EXPIRES;

	/**
	 * @return bool
	 */
	protected function fetchAttributes()
	{
        $tokenData = $this->getAccessTokenData();

		$info = $this->makeSignedRequest('account/verify_credentials.json');

		$this->attributes['id'] = $info['id'];
		$this->attributes['name'] = $info['name'];
		$this->attributes['url'] = 'http://twitter.com/account/redirect_by_id?id=' . $info['id_str'];
        $this->attributes['photo'] = str_replace('_normal', '', $info['profile_image_url']);

        $this->attributes['access_token'] = $tokenData['access_token'];
        $this->attributes['expires'] = $tokenData['expires'];

        $friends_data = $this->makeSignedRequest('friends/ids.json');
        $this->attributes['friend_uids'] = $friends_data['ids'];

		return true;
	}

	/**
	 * Authenticate the user.
	 *
	 * @return boolean whether user was successfuly authenticated.
	 */
	public function authenticate()
	{
		if (isset($_GET['denied'])) {
			$this->cancel();
		}

		return parent::authenticate();
	}
}
