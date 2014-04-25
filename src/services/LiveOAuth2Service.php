<?php
/**
 * LiveOAuth2Service class file.
 *
 * Register application: https://account.live.com/developers/applications/index
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use OAuth\OAuth2\Service\ServiceInterface;
use nodge\eauth\oauth2\Service;

/**
 * Microsoft Live provider class.
 *
 * @package application.extensions.eauth.services
 */
class LiveOAuth2Service extends Service
{

	const SCOPE_BASIC = 'wl.basic';
	const SCOPE_OFFLINE = 'wl.offline_access';
	const SCOPE_SIGNIN = 'wl.signin';
	const SCOPE_BIRTHDAY = 'wl.birthday';
	const SCOPE_CALENDARS = 'wl.calendars';
	const SCOPE_CALENDARS_UPDATE = 'wl.calendars_update';
	const SCOPE_CONTACTS_BIRTHDAY = 'wl.contacts_birthday';
	const SCOPE_CONTACTS_CREATE = 'wl.contacts_create';
	const SCOPE_CONTACTS_CALENDARS = 'wl.contacts_calendars';
	const SCOPE_CONTACTS_PHOTOS = 'wl.contacts_photos';
	const SCOPE_CONTACTS_SKYDRIVE = 'wl.contacts_skydrive';
	const SCOPE_EMAILS = 'wl.emails';
	const sCOPE_EVENTS_CREATE = 'wl.events_create';
	const SCOPE_MESSENGER = 'wl.messenger';
	const SCOPE_PHONE_NUMBERS = 'wl.phone_numbers';
	const SCOPE_PHOTOS = 'wl.photos';
	const SCOPE_POSTAL_ADDRESSES = 'wl.postal_addresses';
	const SCOPE_SHARE = 'wl.share';
	const SCOPE_SKYDRIVE = 'wl.skydrive';
	const SCOPE_SKYDRIVE_UPDATE = 'wl.skydrive_update';
	const SCOPE_WORK_PROFILE = 'wl.work_profile';
	const SCOPE_APPLICATIONS = 'wl.applications';
	const SCOPE_APPLICATIONS_CREATE = 'wl.applications_create';

	protected $name = 'live';
	protected $title = 'Live';
	protected $type = 'OAuth2';
	protected $jsArguments = array('popup' => array('width' => 500, 'height' => 600));

	protected $scopes = array(self::SCOPE_BASIC);
	protected $providerOptions = array(
		'authorize' => 'https://login.live.com/oauth20_authorize.srf',
		'access_token' => 'https://login.live.com/oauth20_token.srf',
	);
	protected $baseApiUrl = 'https://apis.live.net/v5.0/';

	protected function fetchAttributes()
	{
		$info = $this->makeSignedRequest('me');

		$this->attributes['id'] = $info['id'];
		$this->attributes['name'] = $info['name'];
		$this->attributes['url'] = 'https://profile.live.com/cid-' . $info['id'] . '/';

		/*$this->attributes['email'] = $info['emails']['account'];
		$this->attributes['first_name'] = $info['first_name'];
		$this->attributes['last_name'] = $info['last_name'];
		$this->attributes['gender'] = $info['gender'];
		$this->attributes['locale'] = $info['locale'];*/

		return true;
	}

	/**
	 * @return int
	 */
	public function getAuthorizationMethod()
	{
		return ServiceInterface::AUTHORIZATION_METHOD_QUERY_STRING;
	}
}