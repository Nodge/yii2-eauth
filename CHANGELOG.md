Yii2 EAuth Change Log
=====================

### 2.1.4 (11.03.2014)
* Fixed wrong callbackUrl in oauth\ServiceBase when UrlManager uses prettyUrl=false and showScript=false (#12)
* Fixed Yii::t() calls according to Yii2 i18n Named Placeholders (#14)
* Fixed Yii2 refactor #2630 (#15)

### 2.1.3 (30.01.2014)
* Yii2 update (Request methods has been refactored).

### 2.1.2 (17.01.2014)
* Fixed typo in oauth2\ServiceProxy

### 2.1.1 (07.01.2014)
* Fixed scope validation for OAuth services.

### 2.1.0 (22.12.2013)
* Reorganize project with new namespace.
* Assets bundle has been moved.
* Fixed typo in HttpClient (#8).
* Added default User-Agent header to HttpClient.
* Disabled CSRF validation for OpenID callbacks.
* Optimized icons file.
* Added SteamOpenIDService.
* Improved redirect widget.

### 2.0.3 (26.10.2013)
* Fixed redirect_uri when not using url rule (#2).
* Fixed hasValidAccessToken() method for OAuth1 services (#3).
* Fixed auto login cookie (#4).

### 2.0.2 (12.10.2013)
* Fixed ServiceProxy constructor to match its interface (#1).
* Added HttpClient with logging support and curl/streams fallback.
* TokenStorage and HttpClient are configurable now.

### 2.0.1 (08.09.2013)
* Fixed package versions in the composer.json.
* Fixed directories names.
* Added support for custom scope separator in OAuth2 services.
* Added support for additional headers for OAuth2 requests.
* Added method to get error from access token response.
* Added GitHubOAuth2Service.
* Added LinkedinOAuth2Service.
* Added MailruOAuth2Service.
* Added OdnoklassnikiOAuth2Service.
* Added LiveOAuth2Service.
* Added YahooOpenIDService.

### Version 2.0.0 (03.09.2013)
* Use curl for http requests by default.
* getIsAuthenticated() function now looks up for existing access token for all OAuth services.
* Added support for oauth_expires_in to OAuth1 services.
* Added error handlers to OAuth1 services.
* Added support for refresh tokens to OAuth2 ServiceProxy.
* Added an option to disable OAuth2 state validation.

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

### 30.08.2013
* Initial release for Yii2.