Yii2 EAuth Change Log
=====================

### 31.08.2013
* Reorganize directories. Separate root directory by service type.
* Fixed OAuthService::getCallbackUrl(). Now returns url without GET arguments.
* Fixed typos in OAuth services.
* Fixed OpenID loadAttributes functions.
* OAuth2 display mode handling moved to the base class.
* Added OAuthService::getAccessTokenData() method to access to valid access_token and related data.
* Added token default lifetime setting.
* Added "state" argument handling for OAuth2 services to improve security.
* Updated OpenID library. Fixed error with stream requests.
* Added VKontakteOAuth2Service.
* Added GoogleOAuth2Service.
* Added GoogleOAuth2Service.
* Added YandexOAuth2Service.
* Added session token storage using Yii session.

### Version 2.0 (30.08.2013)
* Initial release for Yii2.