<?php

/**
 * EveOnlineOAuth2Service class file.
 *
 * Register application: https://developers.eveonline.com/applications
 *
 * @author Maxim Zemskov <nodge@yandex.ru>, nek <nek@srez.org>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services;

use \nodge\eauth\oauth2\Service;

class EveOnlineOAuth2Service extends Service {

    /**
     * @var string
     */
    protected $name = 'eve';

    /**
     * @var string
     */
    protected $title = 'EVE Online';

    /**
     * @var string
     */
    protected $type = 'OAuth2';

    /**
     * @var array
     */
    protected $jsArguments = ['popup' => ['width' => 680, 'height' => 500]];

    /**
     * @var array
     */
    protected $providerOptions = [
        'authorize'     => 'https://login.eveonline.com/oauth/authorize',
        'access_token'  => 'https://login.eveonline.com/oauth/token'
    ];
    

    /**
     *
     */
    protected function fetchAttributes() {
        $tokenData = $this->getAccessTokenData();
        //
        $info =  (array) $this->makeSignedRequest('https://login.eveonline.com/oauth/verify', [
            'headers'   => [
                'Authorization'=>'Bearer ' . $tokenData['access_token']
            ]
        ]);
        //
        $this->attributes['id']         = $info['CharacterID'];
        $this->attributes['name']       = $info['CharacterName'];
        $this->attributes['char_hash']  = $info['CharacterOwnerHash'];

        // Set full public character info
        $data = $this->getAPIData($info['CharacterID']);
        //
        $this->attributes['race']           = (string)  $data->race;
        $this->attributes['bloodline']      = (string)  $data->bloodline;
        $this->attributes['corporationID']  = (integer) $data->corporationID;
        $this->attributes['corporation']    = (string)  $data->corporation;
        $this->attributes['securityStatus'] = (double)  $data->securityStatus;
        //
        if (isset($data->allianceID)) {
            $this->attributes['allianceID'] = (integer) $data->allianceID;
            $this->attributes['alliance']   = (string)  $data->alliance;
        }
    }

    /**
     * Returns the error array.
     *
     * @param array $response
     * @return array the error array with 2 keys: code and message. Should be null if no errors.
     */
    protected function fetchResponseError($response) {
        if (isset($response['message'])) {
            return [
                'code' => isset($response['error']) ? $response['code'] : 0,
                'message' => $response['message'],
            ];
        } else {
            return null;
        }
    }
    
    /**
     * Get data from EVE API
     * 
     * @param integer $charId
     * @return SimpleXMLElement
     */
    private function getAPIData($charId) {
        $options = [
            'http' => [
                'method' => 'GET',
                'user_agent' => 'yii2-eauth (+https://github.com/Nodge/yii2-eauth)'
            ]
        ];
        $context = stream_context_create($options);
        $url = 'https://api.eveonline.com/eve/CharacterInfo.xml.aspx?characterID=' . (int) $charId;
        $data = file_get_contents($url, false, $context);
        $xml = simplexml_load_string($data);
        return $xml->result;
    }

}
