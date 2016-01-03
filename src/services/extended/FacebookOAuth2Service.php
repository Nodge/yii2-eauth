<?php
/**
 * An example of extending the provider class.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace nodge\eauth\services\extended;

class FacebookOAuth2Service extends \nodge\eauth\services\FacebookOAuth2Service
{

    protected $scopes = [
        self::SCOPE_EMAIL,
        self::SCOPE_USER_BIRTHDAY,
        self::SCOPE_USER_HOMETOWN,
        self::SCOPE_USER_LOCATION,
        self::SCOPE_USER_PHOTOS,
    ];

    /**
     * http://developers.facebook.com/docs/reference/api/user/
     *
     * @see FacebookOAuth2Service::fetchAttributes()
     */
    protected function fetchAttributes()
    {
        $this->attributes = $this->makeSignedRequest('me', [
            'query' => [
                'fields' => join(',', [
                    'id',
                    'name',
                    'link',
                    'email',
                    'verified',
                    'first_name',
                    'last_name',
                    'gender',
                    'birthday',
                    'hometown',
                    'location',
                    'locale',
                    'timezone',
                    'updated_time',
                ])
            ]
        ]);

        $this->attributes['photo_url'] = $this->baseApiUrl.$this->getId().'/picture?width=100&height=100';

        return true;
    }
}
