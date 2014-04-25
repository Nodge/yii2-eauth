<?php
/**
 * GithubOAuth2Service class file.
 *
 * Register application: https://github.com/settings/applications
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use OAuth\Common\Token\TokenInterface;
use OAuth\OAuth2\Service\ServiceInterface;
use nodge\eauth\oauth2\Service;

/**
 * GitHub provider class.
 *
 * @package application.extensions.eauth.services
 */
class GitHubOAuth2Service extends Service
{

	/**
	 * Defined scopes, see http://developer.github.com/v3/oauth/ for definitions
	 */
	const SCOPE_USER = 'user';
	const SCOPE_PUBLIC_REPO = 'public_repo';
	const SCOPE_REPO = 'repo';
	const SCOPE_DELETE_REPO = 'delete_repo';
	const SCOPE_GIST = 'gist';

	protected $name = 'github';
	protected $title = 'GitHub';
	protected $type = 'OAuth2';
	protected $jsArguments = array('popup' => array('width' => 600, 'height' => 450));

	protected $scopes = array();
	protected $providerOptions = array(
		'authorize' => 'https://github.com/login/oauth/authorize',
		'access_token' => 'https://github.com/login/oauth/access_token',
	);
	protected $baseApiUrl = 'https://api.github.com/';

	protected $tokenDefaultLifetime = TokenInterface::EOL_NEVER_EXPIRES;
	protected $errorAccessDeniedCode = 'user_denied';

	protected function fetchAttributes()
	{
		$info = $this->makeSignedRequest('user');

		$this->attributes['id'] = $info['id'];
		$this->attributes['name'] = $info['login'];
		$this->attributes['url'] = $info['html_url'];

		return true;
	}

	/**
	 * Used to configure response type -- we want JSON from github, default is query string format
	 *
	 * @return array
	 */
	public function getExtraOAuthHeaders()
	{
		return array('Accept' => 'application/json');
	}

	/**
	 * Required for GitHub API calls.
	 *
	 * @return array
	 */
	public function getExtraApiHeaders()
	{
		return array('Accept' => 'application/vnd.github.beta+json');
	}

	/**
	 * @return int
	 */
	public function getAuthorizationMethod()
	{
		return ServiceInterface::AUTHORIZATION_METHOD_QUERY_STRING;
	}

	/**
	 * Returns the error array.
	 *
	 * @param array $response
	 * @return array the error array with 2 keys: code and message. Should be null if no errors.
	 */
	protected function fetchResponseError($response)
	{
		if (isset($response['message'])) {
			return array(
				'code' => isset($response['error']) ? $response['code'] : 0,
				'message' => $response['message'],
			);
		} else {
			return null;
		}
	}

}