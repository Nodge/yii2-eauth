<?php
/**
 * FacebookOAuth2Service class file.
 *
 * Register application: https://instagram.com/developer/register/
 *
 * @author PhucPNT. <mail@phucpnt.com>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use nodge\eauth\oauth2\Service;
use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\ServiceInterface;

/**
 * Instagram provider class.
 *
 * @package application.extensions.eauth.services
 */
class InstagramOAuth2Service extends Service
{

	/**
	 * Defined scopes
	 * @link https://instagram.com/developer/authentication/
	 */
	const SCOPE_BASIC = 'basic';
	const SCOPE_COMMENTS = 'comments';
	const SCOPE_RELATIONSHIPS = 'relationships';
	const SCOPE_LIKES = 'likes';

	protected $name = 'instagram';
	protected $title = 'Instagram';
	protected $type = 'OAuth2';
	protected $jsArguments = ['popup' => ['width' => 900, 'height' => 550]];
	protected $popupDisplayName = false;

	protected $scopes = [self::SCOPE_BASIC];
	protected $providerOptions = [
	  'authorize' => 'https://api.instagram.com/oauth/authorize/',
	  'access_token' => 'https://api.instagram.com/oauth/access_token',
	];
	protected $baseApiUrl = 'https://api.instagram.com/v1/';

	protected $tokenDefaultLifetime = TokenInterface::EOL_NEVER_EXPIRES;

	protected function fetchAttributes()
	{
		$info = $this->makeSignedRequest('users/self');
		$data = $info['data'];

		$this->attributes = array_merge($this->attributes, [
		  'id' => $data['id'],
		  'username' => $data['username'],
		  'full_name' => $data['full_name'],
		  'profile_picture' => $data['profile_picture'],
		  'bio' => $data['bio'],
		  'website' => $data['website'],
		  'counts' => $data['counts']
		]);

		return true;
	}

	/**
	 * @return int
	 */
	public function getAuthorizationMethod()
	{
		return ServiceInterface::AUTHORIZATION_METHOD_QUERY_STRING;
	}

	/**
	 * Returns the protected resource.
	 *
	 * @param string $url url to request.
	 * @param array $options HTTP request options. Keys: query, data, referer.
	 * @param boolean $parseResponse Whether to parse response.
	 * @return mixed the response.
	 */
	public function makeSignedRequest($url, $options = [], $parseResponse = true)
	{
		$options['query']['format'] = 'json';
		return parent::makeSignedRequest($url, $options, $parseResponse);
	}

}