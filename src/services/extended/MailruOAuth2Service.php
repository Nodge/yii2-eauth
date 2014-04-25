<?php
/**
 * An example of extending the provider class.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services\extended;

class MailruOAuth2Service extends \nodge\eauth\services\MailruOAuth2Service
{

	protected function fetchAttributes()
	{
		$tokenData = $this->getAccessTokenData();

		$info = $this->makeSignedRequest('/', array(
			'query' => array(
				'uids' => $tokenData['params']['x_mailru_vid'],
				'method' => 'users.getInfo',
				'app_id' => $this->clientId,
			),
		));

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
