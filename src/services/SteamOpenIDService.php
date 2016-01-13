<?php
/**
 * SteamOpenIDService class file.
 *
 * @author Dmitry Ananichev <a@qozz.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use nodge\eauth\openid\Service;

/**
 * Steam provider class.
 *
 * @package application.extensions.eauth.services
 */
class SteamOpenIDService extends Service
{

	protected $name = 'steam';
	protected $title = 'Steam';
	protected $type = 'OpenID';
	protected $jsArguments = ['popup' => ['width' => 990, 'height' => 615]];

	protected $url = 'http://steamcommunity.com/openid/';
    protected $apiUrl = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/';

    public $apiKey;

	protected function fetchAttributes()
	{
        $chunks = explode('/', $this->attributes['id']);
        $id = array_pop($chunks);

        $this->attributes['name'] = $id;

        if ($this->apiKey) {
            $response = $this->makeRequest($this->apiUrl, [
                'query' => [
                    'steamids' => $id,
                    'key' => $this->apiKey,
                    'format' => 'json'
                ]
            ]);

            if (isset($response['response']['players'][0])) {
                $profile = $response['response']['players'][0];

                $this->attributes = array_merge($this->attributes, $profile);
                $this->attributes['name'] = $profile['personaname'];
                $this->attributes['url'] = $profile['profileurl'];
            }
        }
	}

}