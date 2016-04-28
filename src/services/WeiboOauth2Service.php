<?php
/**
 * WeiboOAuth2Service class file.
 * API : @link http://open.weibo.com/wiki/%E5%BE%AE%E5%8D%9AAPI
 * Register application: @link http://open.weibo.com/webmaster/add
 * Oauth2 Weibo desc: @link http://open.weibo.com/wiki/%E6%8E%88%E6%9D%83%E6%9C%BA%E5%88%B6%E8%AF%B4%E6%98%8E
 *
 * @author Maxim Zemskov <alexkomp.ua@gmail.com>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;


use nodge\eauth\ErrorException;
use nodge\eauth\oauth2\Service;
use OAuth\OAuth2\Service\ServiceInterface;
use Yii;

class WeiboOauth2Service extends Service
{
    /**
     * Scopes @link http://open.weibo.com/wiki/Scope
     */
    const SCOPE_ALL = 'all';

    protected $name = 'weibo';
    protected $title = 'Weibo';
    protected $type = 'OAuth2';
    protected $jsArguments = ['popup' => ['width' => 585, 'height' => 290]];

    protected $scopes = [];
    protected $providerOptions = [
        'authorize' => 'https://api.weibo.com/oauth2/authorize',
        'access_token' => 'https://api.weibo.com/oauth2/access_token',
    ];
    protected $baseApiUrl = 'https://api.weibo.com/2/';

    /**
     * {@inheritdoc}
     */
    protected function fetchAttributes()
    {
        $accessTokenData = $this->getAccessTokenData();
        if (isset($accessTokenData['params']['uid'])) {
            $this->attributes['id'] = $accessTokenData['params']['uid'];
        } else {
            throw new ErrorException(Yii::t('eauth', 'Unable to make user info request without uid.'));
        }
        $info = $this->makeSignedRequest('users/show.json', [
            'query' => [
                'uid' => $this->attributes['id']
            ]
        ]);

        foreach(['name', 'gender'] as $attr) {
            $this->attributes[$attr] = $info[$attr];
        }
        $this->attributes['url'] = 'http://www.weibo.com/' . $info['profile_url'];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationMethod()
    {
        return ServiceInterface::AUTHORIZATION_METHOD_QUERY_STRING;
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchResponseError($response)
    {
        if (isset($response['error'])) {
            return [
                'code' => $response['error_code'],
                'message' => $response['error'],
            ];
        }
        return null;
    }
}