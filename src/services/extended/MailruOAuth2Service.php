<?php
/**
 * An example of extending the provider class.
 *
 * @author Pyrotech Solutions <developers@pyrotechsolutions.com>
 * @link http://github.com/PyrotechSolutions/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace ptech\eauth\services\extended;

class MailruOAuth2Service extends \ptech\eauth\services\MailruOAuth2Service
{

	protected function fetchAttributes()
	{
		$tokenData = $this->getAccessTokenData();

		$info = $this->makeSignedRequest('/', [
			'query' => [
				'uids' => $tokenData['params']['x_mailru_vid'],
				'method' => 'users.getInfo',
				'app_id' => $this->clientId,
			],
		]);

		$info = $info[0];

		$this->attributes['id'] = $info['uid'];
		$this->attributes['name'] = $info['first_name'] . ' ' . $info['last_name'];
		$this->attributes['first_name'] = $info['first_name'];
		$this->attributes['last_name'] = $info['last_name'];
		$this->attributes['url'] = $info['link'];
		$this->attributes['photo'] = $info['pic'];

		return true;
	}

}
