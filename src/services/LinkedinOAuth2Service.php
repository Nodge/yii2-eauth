<?php
/**
 * LinkedinOAuth2Service class file.
 *
 * Register application: https://www.linkedin.com/secure/developer
 * Note: Integration URL should be filled with a valid callback url.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use OAuth\OAuth2\Service\ServiceInterface;
use nodge\eauth\oauth2\Service;

/**
 * LinkedIn provider class.
 *
 * @package application.extensions.eauth.services
 */
class LinkedinOAuth2Service extends Service
{

	/**
	 * Defined scopes
	 *
	 * @link http://developer.linkedin.com/documents/authentication#granting
	 */
	const SCOPE_R_BASICPROFILE = 'r_basicprofile';
	const SCOPE_R_FULLPROFILE = 'r_fullprofile';
	const SCOPE_R_EMAILADDRESS = 'r_emailaddress';
	const SCOPE_R_NETWORK = 'r_network';
	const SCOPE_R_CONTACTINFO = 'r_contactinfo';
	const SCOPE_RW_NUS = 'rw_nus';
	const SCOPE_RW_GROUPS = 'rw_groups';
	const SCOPE_W_MESSAGES = 'w_messages';

	protected $name = 'linkedin_oauth2';
	protected $title = 'LinkedIn';
	protected $type = 'OAuth2';
	protected $jsArguments = array('popup' => array('width' => 900, 'height' => 550));

	protected $scopes = array(self::SCOPE_R_BASICPROFILE);
	protected $providerOptions = array(
		'authorize' => 'https://www.linkedin.com/uas/oauth2/authorization',
		'access_token' => 'https://www.linkedin.com/uas/oauth2/accessToken',
	);
	protected $baseApiUrl = 'https://api.linkedin.com/v1/';

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
	 * @return int
	 */
	public function getAuthorizationMethod()
	{
		return ServiceInterface::AUTHORIZATION_METHOD_QUERY_STRING_V2;
	}
}