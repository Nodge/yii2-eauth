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


use nodge\eauth\ErrorException;
use nodge\eauth\oauth2\Service;
use OAuth\OAuth2\Service\ServiceInterface;
use Yii;

class QQOauth2Service extends Service
{
    /**
     * Scopes described here
     * @link http://wiki.connect.qq.com/api%E5%88%97%E8%A1%A8
     */
    const SCOPE_USER_INFO = 'get_user_info';
    const SCOPE_VIP_INFO = 'get_vip_info';
    const SCOPE_VIP_RICH_INFO = 'get_vip_rich_info';

    const SCOPE_LIST_ALBUM = 'list_album';
    const SCOPE_UPLOAD_PIC = 'upload_pic';
    const SCOPE_ADD_ALBUM = 'add_album';
    const SCOPE_LIST_PHOTO = 'list_photo';

    protected $name = 'qq';
    protected $title = 'QQ.com';
    protected $type = 'OAuth2';
    protected $jsArguments = [
        'popup' => ['width' => 585, 'height' => 350],
    ];
    protected $scopeSeparator = ',';
    protected $scopes = [self::SCOPE_USER_INFO];

    protected $providerOptions = [
        'authorize' => 'https://graph.qq.com/oauth2.0/authorize',
        'access_token' => 'https://graph.qq.com/oauth2.0/token',
        'open_id' => 'https://graph.qq.com/oauth2.0/me'
    ];
    protected $baseApiUrl = 'https://graph.qq.com/';

    protected $openId;

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationMethod() {
        return ServiceInterface::AUTHORIZATION_METHOD_QUERY_STRING;
    }

    /**
     * {@inheritdoc}
     */
    public function parseAccessTokenResponse($response)
    {
        parse_str($response, $output);
        return $output;
    }

    /**
     * qq api requires additional openid param
     *
     * {@inheritdoc}
     */
    public function authenticate()
    {
        if (parent::authenticate()) {
            $this->requestOpenId();
            return true;
        }
        return false;
    }

    /**
     * stores user's openid in Token as extra data
     */
    protected function requestOpenId()
    {
        $response = $this->makeSignedRequest($this->providerOptions['open_id'], [], false);
        $openid = $this->parseOpenIdResponse($response);
        $tokenStorage = $this->getTokenStorage();
        $token = $tokenStorage->retrieveAccessToken($this->name);
        $token->setExtraParams(['openid' => $openid]);
        $tokenStorage->storeAccessToken($this->name, $token);
    }

    /**
     * @return string|null User's openid
     */
    protected function getOpenId()
    {
        $token = $this->getTokenStorage()->retrieveAccessToken($this->name);
        $tokenParams = $token->getExtraParams();
        return isset($tokenParams['openid']) ? $tokenParams['openid'] : null;
    }

    /**
     * qq api requires openid and client_id on api requests
     *
     * {@inheritdoc}
     */
    public function makeSignedRequest($url, $options = [], $parseResponse = true)
    {
        if (!$this->getIsAuthenticated()) {
            throw new ErrorException(Yii::t('eauth', 'Unable to complete the signed request because the user was not authenticated.'), 401);
        }
        if ($openid = $this->getOpenId()) {
            $options['query']['openid'] = $openid;
        }
        $options['query']['oauth_consumer_key'] = $this->clientId;
        return parent::makeSignedRequest($url, $options, $parseResponse);
    }

    /**
     * @param $response
     * @return string
     */
    protected function parseOpenIdResponse($response)
    {
        preg_match('/\{[^\}]*\}/', $response, $match);
        $decoded = json_decode($match[0]);
        return $decoded->openid;
    }

    /**
     * {@inheritdoc}
     */
    protected function fetchAttributes()
    {
        $info = $this->makeSignedRequest('user/get_user_info');
        $this->attributes['id'] = $this->getOpenId();
        $this->attributes['name'] = $info['nickname'];
        return true;
    }

    /**
     * @param array $response
     * @return array|null
     */
    protected function fetchResponseError($response)
    {
        if (isset($response['ret']) && $response['ret'] !== 0) {
            return [
                'code' => $response['ret'],
                'message' => $response['msg'],
            ];
        }
        return null;
    }
}