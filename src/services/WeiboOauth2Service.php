<?php
/**
 * WeiboOAuth2Service class file.
 * API : @link http://open.weibo.com
 * Register application: @link http://open.weibo.com/webmaster/add
 * Oauth2 Weibo desc: @link http://open.weibo.com/wiki/%E6%8E%88%E6%9D%83%E6%9C%BA%E5%88%B6%E8%AF%B4%E6%98%8E
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;


use nodge\eauth\oauth2\Service;

class WeiboOauth2Service extends Service
{
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
}