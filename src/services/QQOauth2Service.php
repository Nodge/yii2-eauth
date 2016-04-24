<?php
/**
 * QQOAuth2Service class file.
 *
 * Register application: http://api.mail.ru/sites/my/add
 *
 * @author Aleksandr Kompaniiets <alexkomp.ua@gmail.com>
 * @link http://connect.qq.com/manage/index
 * @link http://wiki.connect.qq.com/%E4%BD%BF%E7%94%A8authorization_code%E8%8E%B7%E5%8F%96access_token
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;


use nodge\eauth\oauth2\Service;

class QQOauth2Service extends Service
{
    const SCOPE_USER_INFO = 'get_user_info';

    protected $name = 'qq';
    protected $title = 'QQ.com';
    protected $type = 'OAuth2';
    protected $jsArguments = ['popup' => ['width' => 585, 'height' => 350]];

    protected $scopes = [self::SCOPE_USER_INFO];
    protected $providerOptions = [
        'authorize' => 'https://graph.qq.com/oauth2.0/authorize',
        'access_token' => 'https://graph.qq.com/oauth2.0/token',
    ];
    protected $baseApiUrl = 'https://graph.qq.com/';
}