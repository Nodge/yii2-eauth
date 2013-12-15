<?php
/**
 * GoogleOAuth2Service class file.
 *
 * Register application: https://code.google.com/apis/console/
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use nodge\eauth\oauth2\Service;

/**
 * Google provider class.
 *
 * @package application.extensions.eauth.services
 */
class GoogleOAuth2Service extends Service {

	/**
	 * Defined scopes -- Google has way too many Application Programming Interfaces
	 */
	const SCOPE_ADSENSE = 'https://www.googleapis.com/auth/adsense';
	const SCOPE_GAN = 'https://www.googleapis.com/auth/gan'; // google affiliate network...?
	const SCOPE_ANALYTICS = 'https://www.googleapis.com/auth/analytics.readonly';
	const SCOPE_BOOKS = 'https://www.googleapis.com/auth/books';
	const SCOPE_BLOGGER = 'https://www.googleapis.com/auth/blogger';
	const SCOPE_CALENDAR = 'https://www.googleapis.com/auth/calendar';
	const SCOPE_CLOUDSTORAGE = 'https://www.googleapis.com/auth/devstorage.read_write';
	const SCOPE_CONTACT = 'https://www.google.com/m8/feeds/';
	const SCOPE_CONTENTFORSHOPPING = 'https://www.googleapis.com/auth/structuredcontent'; // what even is this
	const SCOPE_CHROMEWEBSTORE = 'https://www.googleapis.com/auth/chromewebstore.readonly';
	const SCOPE_DOCUMENTSLIST = 'https://docs.google.com/feeds/';
	const SCOPE_GOOGLEDRIVE = 'https://www.googleapis.com/auth/drive';
	const SCOPE_GOOGLEDRIVE_FILES = 'https://www.googleapis.com/auth/drive.file';
	const SCOPE_GMAIL = 'https://mail.google.com/mail/feed/atom';
	const SCOPE_GPLUS = 'https://www.googleapis.com/auth/plus.me';
	const SCOPE_GROUPS_PROVISIONING = 'https://apps-apis.google.com/a/feeds/groups/';
	const SCOPE_GOOGLELATITUDE = 'https://www.googleapis.com/auth/latitude.all.best https://www.googleapis.com/auth/latitude.all.city'; // creepy stalker api...
	const SCOPE_MODERATOR = 'https://www.googleapis.com/auth/moderator';
	const SCOPE_NICKNAME_PROVISIONING = 'https://apps-apis.google.com/a/feeds/alias/';
	const SCOPE_ORKUT = 'https://www.googleapis.com/auth/orkut'; // evidently orkut still exists. who knew?
	const SCOPE_PICASAWEB = 'https://picasaweb.google.com/data/';
	const SCOPE_SITES = 'https://sites.google.com/feeds/';
	const SCOPE_SPREADSHEETS = 'https://spreadsheets.google.com/feeds/';
	const SCOPE_TASKS = 'https://www.googleapis.com/auth/tasks';
	const SCOPE_URLSHORTENER = 'https://www.googleapis.com/auth/urlshortener';
	const SCOPE_USERINFO_EMAIL = 'https://www.googleapis.com/auth/userinfo.email';
	const SCOPE_USERINFO_PROFILE = 'https://www.googleapis.com/auth/userinfo.profile';
	const SCOPE_USER_PROVISIONING = 'https://apps-apis.google.com/a/feeds/user/';
	const SCOPE_WEBMASTERTOOLS = 'https://www.google.com/webmasters/tools/feeds/';
	const SCOPE_YOUTUBE = 'https://gdata.youtube.com';

	protected $name = 'google_oauth';
	protected $title = 'Google';
	protected $type = 'OAuth2';
	protected $jsArguments = array('popup' => array('width' => 500, 'height' => 450));

	protected $scopes = array(self::SCOPE_USERINFO_PROFILE);
	protected $providerOptions = array(
		'authorize' => 'https://accounts.google.com/o/oauth2/auth',
		'access_token' => 'https://accounts.google.com/o/oauth2/token',
	);

	protected function fetchAttributes() {
		$info = $this->makeSignedRequest('https://www.googleapis.com/oauth2/v1/userinfo');

		$this->attributes['id'] = $info['id'];
		$this->attributes['name'] = $info['name'];

		if (!empty($info['link'])) {
			$this->attributes['url'] = $info['link'];
		}

		/*if (!empty($info['gender']))
			$this->attributes['gender'] = $info['gender'] == 'male' ? 'M' : 'F';
		
		if (!empty($info['picture']))
			$this->attributes['photo'] = $info['picture'];
		
		$info['given_name']; // first name
		$info['family_name']; // last name
		$info['birthday']; // format: 0000-00-00
		$info['locale']; // format: en*/
	}

	/**
	 * Returns the protected resource.
	 *
	 * @param string $url url to request.
	 * @param array $options HTTP request options. Keys: query, data, referer.
	 * @param boolean $parseResponse Whether to parse response.
	 * @return mixed the response.
	 */
	public function makeSignedRequest($url, $options = array(), $parseResponse = true) {
		if (!isset($options['query']['alt'])) {
			$options['query']['alt'] = 'json';
		}
		return parent::makeSignedRequest($url, $options, $parseResponse);
	}

	/**
	 * Returns the error array.
	 * @param array $response
	 * @return array the error array with 2 keys: code and message. Should be null if no errors.
	 */
	protected function fetchResponseError($response) {
		if (isset($response['error'])) {
			return array(
				'code' => $response['error']['code'],
				'message' => $response['error']['message'],
			);
		}
		else {
			return null;
		}
	}
}