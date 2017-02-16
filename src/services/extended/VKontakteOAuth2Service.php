<?php
/**
 * An example of extending the provider class.
 *
 * @author Pyrotech Solutions <developers@pyrotechsolutions.com>
 * @link http://github.com/PyrotechSolutions/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace ptech\eauth\services\extended;

class VKontakteOAuth2Service extends \ptech\eauth\services\VKontakteOAuth2Service
{

	// protected $scope = 'friends';

	protected function fetchAttributes()
	{
		$tokenData = $this->getAccessTokenData();
		$info = $this->makeSignedRequest('users.get.json', [
			'query' => [
				'uids' => $tokenData['params']['user_id'],
				//'fields' => '', // uid, first_name and last_name is always available
				'fields' => 'nickname, sex, bdate, city, country, timezone, photo, photo_medium, photo_big, photo_rec',
			],
		]);

		$info = $info['response'][0];

		$this->attributes = $info;
		$this->attributes['id'] = $info['uid'];
		$this->attributes['name'] = $info['first_name'] . ' ' . $info['last_name'];
		$this->attributes['url'] = 'http://vk.com/id' . $info['uid'];

		if (!empty($info['nickname'])) {
			$this->attributes['username'] = $info['nickname'];
		} else {
			$this->attributes['username'] = 'id' . $info['uid'];
		}

		$this->attributes['gender'] = $info['sex'] == 1 ? 'F' : 'M';

		if (!empty($info['timezone'])) {
			$this->attributes['timezone'] = timezone_name_from_abbr('', $info['timezone'] * 3600, date('I'));
		}

		return true;
	}
}
