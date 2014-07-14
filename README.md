Yii2 EAuth extension
====================

EAuth extension allows to authenticate users with accounts on other websites.
Supported protocols: OpenID, OAuth 1.0 and OAuth 2.0.

EAuth is a extension for provide a unified (does not depend on the selected service) method to authenticate the user. So, the extension itself does not perform login, does not register the user and does not bind the user accounts from different providers.

* [Demo](http://nodge.ru/yii-eauth/demo2/)
* [Demo project](https://github.com/Nodge/yii2-eauth-demo/)
* [Installation](#installation)
* [Version for yii 1.1](https://github.com/Nodge/yii-eauth/)

### Why own extension and not a third-party service?
The implementation of the authorization on your own server has several advantages:

* Full control over the process: what will be written in the authorization window, what data we get, etc.
* Ability to change the appearance of the widget.
* When logging via OAuth is possible to invoke methods on API.
* Fewer dependencies on third-party services - more reliable application.


### The extension allows you to:

* Ignore the nuances of authorization through the different types of services, use the class based adapters for each service.
* Get a unique user ID that can be used to register user in your application.
* Extend the standard authorization classes to obtain additional data about the user.
* Work with the API of social networks by extending the authorization classes.
* Set up a list of supported services, customize the appearance of the widget, use the popup window without closing your application.


### Extension includes:

* The component that contains utility functions.
* A widget that displays a list of services in the form of icons and allowing authorization in the popup window.
* Base classes to create your own services.
* Ready for authenticate via Google, Twitter, Facebook and other providers.


### Included services:

* OpenID:
	* Google
	* Yahoo
	* Yandex (ru)
	* Steam
* OAuth1:
	* Twitter
	* LinkedIn
* OAuth2:
	* Google
	* Facebook
	* Live
	* GitHub
	* LinkedIn
	* Yandex (ru)
	* VKontake (ru)
	* Mail.ru (ru)
	* Odnoklassniki (ru)


### Resources

* [Yii EAuth](https://github.com/Nodge/yii2-eauth)
* [Demo](http://nodge.ru/yii-eauth/demo2/)
* [Demo project](https://github.com/Nodge/yii2-eauth-demo/)
* [Yii Framework](http://yiiframework.com/)
* [OpenID](http://openid.net/)
* [OAuth](http://oauth.net/)
* [OAuth 2.0](http://oauth.net/2/)
* [LightOpenID](https://github.com/iignatov/LightOpenID)
* [PHPoAuthLib](https://github.com/Lusitanian/PHPoAuthLib)


### Requirements

* Yii 2.0 or above
* curl php extension
* LightOpenId
* PHPoAuthLib


# Installation

This library can be found on [Packagist](https://packagist.org/packages/nodge/yii2-eauth).
The recommended way to install this is through [composer](http://getcomposer.org).

Edit your `composer.json` and add:

```json
{
    "require": {
        "nodge/yii2-eauth": "~2.0"
    }
}
```

And install dependencies:

```bash
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```


# Usage

## Demo project

The source code of the [demo](http://nodge.ru/yii-eauth/demo2/) is available [here](https://github.com/Nodge/yii2-eauth-demo/).


## Basic setup

### Configuration

Add the following in your config:

```php
<?php
...
	'components'=>array(
		'eauth' => array(
			'class' => 'nodge\eauth\EAuth',
			'popup' => true, // Use the popup window instead of redirecting.
			'cache' => false, // Cache component name or false to disable cache. Defaults to 'cache' on production environments.
			'cacheExpire' => 0, // Cache lifetime. Defaults to 0 - means unlimited.
			'httpClient' => array(
				// uncomment this to use streams in safe_mode
				//'useStreamsFallback' => true,
			),
			'services' => array( // You can change the providers and their classes.
				'google' => array(
					'class' => 'nodge\eauth\services\GoogleOpenIDService',
					//'realm' => '*.example.org', // your domain, can be with wildcard to authenticate on subdomains.
				),
				'yandex' => array(
					'class' => 'nodge\eauth\services\YandexOpenIDService',
					//'realm' => '*.example.org', // your domain, can be with wildcard to authenticate on subdomains.
				),
				'twitter' => array(
					// register your app here: https://dev.twitter.com/apps/new
					'class' => 'nodge\eauth\services\TwitterOAuth1Service',
					'key' => '...',
					'secret' => '...',
				),
				'google_oauth' => array(
					// register your app here: https://code.google.com/apis/console/
					'class' => 'nodge\eauth\services\GoogleOAuth2Service',
					'clientId' => '...',
					'clientSecret' => '...',
					'title' => 'Google (OAuth)',
				),
				'yandex_oauth' => array(
					// register your app here: https://oauth.yandex.ru/client/my
					'class' => 'nodge\eauth\services\YandexOAuth2Service',
					'clientId' => '...',
					'clientSecret' => '...',
					'title' => 'Yandex (OAuth)',
				),
				'facebook' => array(
					// register your app here: https://developers.facebook.com/apps/
					'class' => 'nodge\eauth\services\FacebookOAuth2Service',
					'clientId' => '...',
					'clientSecret' => '...',
				),
				'yahoo' => array(
					'class' => 'nodge\eauth\services\YahooOpenIDService',
					//'realm' => '*.example.org', // your domain, can be with wildcard to authenticate on subdomains.
				),
				'linkedin' => array(
					// register your app here: https://www.linkedin.com/secure/developer
					'class' => 'nodge\eauth\services\LinkedinOAuth1Service',
					'key' => '...',
					'secret' => '...',
					'title' => 'LinkedIn (OAuth1)',
				),
				'linkedin_oauth2' => array(
					// register your app here: https://www.linkedin.com/secure/developer
					'class' => 'nodge\eauth\services\LinkedinOAuth2Service',
					'clientId' => '...',
					'clientSecret' => '...',
					'title' => 'LinkedIn (OAuth2)',
				),
				'github' => array(
					// register your app here: https://github.com/settings/applications
					'class' => 'nodge\eauth\services\GitHubOAuth2Service',
					'clientId' => '...',
					'clientSecret' => '...',
				),
				'live' => array(
					// register your app here: https://account.live.com/developers/applications/index
					'class' => 'nodge\eauth\services\LiveOAuth2Service',
					'clientId' => '...',
					'clientSecret' => '...',
				),
				'steam' => array(
					'class' => 'nodge\eauth\services\SteamOpenIDService',
					//'realm' => '*.example.org', // your domain, can be with wildcard to authenticate on subdomains.
				),
				'vkontakte' => array(
					// register your app here: https://vk.com/editapp?act=create&site=1
					'class' => 'nodge\eauth\services\VKontakteOAuth2Service',
					'clientId' => '...',
					'clientSecret' => '...',
				),
				'mailru' => array(
					// register your app here: http://api.mail.ru/sites/my/add
					'class' => 'nodge\eauth\services\MailruOAuth2Service',
					'clientId' => '...',
					'clientSecret' => '...',
				),
				'odnoklassniki' => array(
					// register your app here: http://dev.odnoklassniki.ru/wiki/pages/viewpage.action?pageId=13992188
					// ... or here: http://www.odnoklassniki.ru/dk?st.cmd=appsInfoMyDevList&st._aid=Apps_Info_MyDev
					'class' => 'nodge\eauth\services\OdnoklassnikiOAuth2Service',
					'clientId' => '...',
					'clientSecret' => '...',
					'clientPublic' => '...',
					'title' => 'Odnoklas.',
				),
			),
		),

		// (optionally) you can configure pretty urls
		'urlManager' => array(
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => array(
				'login/<service:google|facebook|etc>' => 'site/login',
			),
		),

		// (optionally) you can configure logging
		'log' => array(
			'targets' => array(
				array(
					'class' => 'yii\log\FileTarget',
					'logFile' => '@app/runtime/logs/eauth.log',
					'categories' => array('nodge\eauth\*'),
					'logVars' => array(),
				),
			),
		),
		...
	),
...
```

### User model

You need to modify your User model to login with EAuth services.
Example from demo project:

```php
<?php
...
	/**
	 * @var array EAuth attributes
	 */
	public $profile;

	public static function findIdentity($id) {
		if (Yii::$app->getSession()->has('user-'.$id)) {
			return new self(Yii::$app->getSession()->get('user-'.$id));
		}
		else {
			return isset(self::$users[$id]) ? new self(self::$users[$id]) : null;
		}
	}

	/**
	 * @param \nodge\eauth\ServiceBase $service
	 * @return User
	 * @throws ErrorException
	 */
	public static function findByEAuth($service) {
		if (!$service->getIsAuthenticated()) {
			throw new ErrorException('EAuth user should be authenticated before creating identity.');
		}

		$id = $service->getServiceName().'-'.$service->getId();
		$attributes = array(
			'id' => $id,
			'username' => $service->getAttribute('name'),
			'authKey' => md5($id),
			'profile' => $service->getAttributes(),
		);
		$attributes['profile']['service'] = $service->getServiceName();
		Yii::$app->getSession()->set('user-'.$id, $attributes);
		return new self($attributes);
	}
...
```

Then you can access to EAuth attributes through:

```php
<?php
	$identity = Yii::$app->getUser()->getIdentity();
	if (isset($identity->profile)) {
		VarDumper::dump($identity->profile, 10, true);
	}
```

### Controller

Attach OpenID Controller behavior to disable CSRF validation for OpenID callbacks.
Or you can disable CSRF validation by yourself.

```php
<?php
...
	public function behaviors() {
    		return array(
    			'eauth' => array(
    				// required to disable csrf validation on OpenID requests
    				'class' => \nodge\eauth\openid\ControllerBehavior::className(),
    				'only' => array('login'),
    			),
    		);
    	}
...
```

Add the following to your Login action:

```php
<?php
...
	public function actionLogin() {
		$serviceName = Yii::$app->getRequest()->getQueryParam('service');
		if (isset($serviceName)) {
			/** @var $eauth \nodge\eauth\ServiceBase */
			$eauth = Yii::$app->get('eauth')->getIdentity($serviceName);
			$eauth->setRedirectUrl(Yii::$app->getUser()->getReturnUrl());
			$eauth->setCancelUrl(Yii::$app->getUrlManager()->createAbsoluteUrl('site/login'));

			try {
				if ($eauth->authenticate()) {
//					var_dump($eauth->getIsAuthenticated(), $eauth->getAttributes()); exit;

					$identity = User::findByEAuth($eauth);
					Yii::$app->getUser()->login($identity);

					// special redirect with closing popup window
					$eauth->redirect();
				}
				else {
					// close popup window and redirect to cancelUrl
					$eauth->cancel();
				}
			}
			catch (\nodge\eauth\ErrorException $e) {
				// save error to show it later
				Yii::$app->getSession()->setFlash('error', 'EAuthException: '.$e->getMessage());

				// close popup window and redirect to cancelUrl
//				$eauth->cancel();
				$eauth->redirect($eauth->getCancelUrl());
			}
		}

		// default authorization code through login/password ..
	}
...
```

### View

```php
...
<?php
	if (Yii::$app->getSession()->hasFlash('error')) {
		echo '<div class="alert alert-danger">'.Yii::$app->getSession()->getFlash('error').'</div>';
	}
?>
...
<p class="lead">Do you already have an account on one of these sites? Click the logo to log in with it here:</p>
<?php echo \nodge\eauth\Widget::widget(array('action' => 'site/login')); ?>
...
```


## Extending

To receive all the necessary data to your application, you can override the base class of any provider.
Base classes are stored in `@eauth/src/services`.
Examples of extended classes can be found in `@eauth/src/services/extended/`.

After overriding the base class, you need to update your configuration file with a new class name.


## Working with OAuth API

You can extend base classes with necessary methods and then write something like this:

```php
<?php
	/** @var $eauth EAuthServiceBase */
	$eauth = Yii::$app->eauth->getIdentity('facebook');

	// to get protected resources user should be authenticated:
	if ($eauth->getIsAuthenticated()) {
		$eauth->callProtectedApiMethod();
		$eauth->callAnotherProtectedApiMethod();
	}

	// or you can get public resources at any time:
	$eauth->callPublicApiMethod();
	$eauth->callAnotherPublicApiMethod();
```

Example of a API call method:

```php
<?php
	class FacebookOAuth2Service extends \nodge\eauth\services\FacebookOAuth2Service
	{
		public function fooApiMethod($bar) {
			$api_method = 'me'; // ex. for Facebook this results to https://graph.facebook.com/me

			// get protected resource
			$response = $this->makeSignedRequest($api_method, [
				'query' => [ 'foo' => 'bar' ], // GET arguments
				'data' => [ 'foo' => 'bar' ], // POST arguments
				'headers' => [ 'X-Foo' => 'bar'  ], // Extra HTTP headers
			]);

			// you can get public resources with the same API:
			//$response = $this->makeRequest($api_method, $options);

			// process $response
			$data = process($response);

			// return results
			return $data;
		}
	}
```

API calls are performed if the current user has a valid access token (saved during the authentication).
You can save access_token to your database by using custom token storage in your config:

```php
<?php
...
	'components'=>array(
		'eauth' => array(
			'class' => 'nodge\eauth\EAuth',
			'tokenStorage' => array(
				'class' => '@app\eauth\DatabaseTokenStorage',
			),
		),
		...
	),
...
```


## Translation

To use translations add the following in your config:

```php
<?php
...
	'components'=>array(
		'i18n' => array(
			'translations' => array(
				'eauth' => array(
					'class' => 'yii\i18n\PhpMessageSource',
					'basePath' => '@eauth/messages',
				),
			),
		),
		...
	),
...
```

Available translations can be found in `@eauth/src/messages`.


# License

The extension was released under the [New BSD License](http://www.opensource.org/licenses/bsd-license.php), so you'll find the latest version on [GitHub](https://github.com/Nodge/yii2-eauth).
