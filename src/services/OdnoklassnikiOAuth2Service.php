<?php
/**
 * OdnoklassnikiOAuthService class file.
 *
 * Register application: http://dev.odnoklassniki.ru/wiki/pages/viewpage.action?pageId=13992188
 * Manage your applications: http://www.odnoklassniki.ru/dk?st.cmd=appsInfoMyDevList&st._aid=Apps_Info_MyDev
 * Note: Enabling this service a little more difficult because of the authorization policy of the service.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use nodge\eauth\oauth2\Service;

/**
 * Odnoklassniki.Ru provider class.
 *
 * @package application.extensions.eauth.services
 */
class OdnoklassnikiOAuth2Service extends Service
{

	const SCOPE_VALUABLE_ACCESS = 'VALUABLE ACCESS';
	const SCOPE_SET_STATUS = 'SET STATUS';
	const SCOPE_PHOTO_CONTENT = 'PHOTO CONTENT';

	protected $name = 'odnoklassniki';
	protected $title = 'Odnoklassniki';
	protected $type = 'OAuth2';
	protected $jsArguments = array('popup' => array('width' => 680, 'height' => 500));

	protected $clientPublic;
	protected $scopes = array();
	protected $scopeSeparator = ';';
	protected $providerOptions = array(
		'authorize' => 'http://www.odnoklassniki.ru/oauth/authorize',
		'access_token' => 'http://api.odnoklassniki.ru/oauth/token.do',
	);
	protected $baseApiUrl = 'http://api.odnoklassniki.ru/fb.do';

	protected $tokenDefaultLifetime = 1500; // about 25 minutes
	protected $validateState = false;

	protected function fetchAttributes()
	{
		$info = $this->makeSignedRequest('', array(
			'query' => array(
				'method' => 'users.getCurrentUser',
				'format' => 'JSON',
				'application_key' => $this->clientPublic,
				'client_id' => $this->clientId,
			),
		));

		$this->attributes['id'] = $info['uid'];
		$this->attributes['name'] = $info['first_name'] . ' ' . $info['last_name'];

		return true;
	}

	/**
	 * @return string
	 */
	public function getClientPublic()
	{
		return $this->clientPublic;
	}

	/**
	 * @param string $clientPublic
	 */
	public function setClientPublic($clientPublic)
	{
		$this->clientPublic = $clientPublic;
	}

	/**
	 * Returns the protected resource.
	 *
	 * @param string $url url to request.
	 * @param array $options HTTP request options. Keys: query, data, referer.
	 * @param boolean $parseResponse Whether to parse response.
	 * @return mixed the response.
	 */
	public function makeSignedRequest($url, $options = array(), $parseResponse = true)
	{
		$token = $this->getAccessTokenData();
		if (isset($token)) {
			$params = '';
			ksort($options['query']);
			foreach ($options['query'] as $k => $v) {
				$params .= $k . '=' . $v;
			}
			$options['query']['sig'] = md5($params . md5($token['access_token'] . $this->clientSecret));
			$options['query']['access_token'] = $token['access_token'];
		}
		return parent::makeSignedRequest($url, $options, $parseResponse);
	}

	/**
	 * Returns the error array.
	 *
	 * @param array $response
	 * @return array the error array with 2 keys: code and message. Should be null if no errors.
	 */
	protected function fetchResponseError($response)
	{
		if (isset($response['error_code'])) {
			return array(
				'code' => $response['error_code'],
				'message' => $response['error_msg'],
			);
		} else {
			return null;
		}
	}

}
