<?php
/**
 * An example of extending the provider class.
 *
 * @author Pyrotech Solutions <developers@pyrotechsolutions.com>
 * @link http://github.com/PyrotechSolutions/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace ptech\eauth\services\extended;

class OdnoklassnikiOAuth2Service extends \ptech\eauth\services\OdnoklassnikiOAuth2Service
{

	protected $scopes = [self::SCOPE_VALUABLE_ACCESS];

	protected function fetchAttributes()
	{
		parent::fetchAttributes();

		$info = $this->makeSignedRequest('', [
			'query' => [
				'method' => 'users.getInfo',
				'uids' => $this->attributes['id'],
				'fields' => 'url_profile',
				'format' => 'JSON',
				'application_key' => $this->clientPublic,
				'client_id' => $this->clientId,
			],
		]);

		preg_match('/\d+\/{0,1}$/', $info[0]->url_profile, $matches);
		$this->attributes['id'] = (int)$matches[0];
		$this->attributes['url'] = $info[0]->url_profile;

		return true;
	}


	/**
	 * @param string $link
	 * @param string $message
	 * @return array
	 */
	public function wallPost($link, $message)
	{
		return $this->makeSignedRequest('', [
			'query' => [
				'application_key' => $this->clientPublic,
				'method' => 'share.addLink',
				'format' => 'JSON',
				'linkUrl' => $link,
				'comment' => $message,
			],
		]);
	}

}
