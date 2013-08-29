Yii2 EAuth extension
====================

EAuth extension allows to authenticate users with accounts on other websites.
Supported protocols: OpenID, OAuth 1.0 and OAuth 2.0.

EAuth is a extension for provide a unified (does not depend on the selected service) method to authenticate the user. So, the extension itself does not perform login, does not register the user and does not bind the user accounts from different providers.


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


### Supported providers "out of box":

* OpenID: Google, Yandex(ru)
* OAuth: Twitter, LinkedIn
* OAuth 2.0: Google, Facebook, Live, GitHub, LinkedIn, VKontake(ru), Mail.ru(ru), Moi Krug(ru), Odnoklassniki(ru)


### Resources

* [Yii EAuth](https://github.com/Nodge/yii-eauth)
* [Yii Framework](http://yiiframework.com/)
* [OpenID](http://openid.net/)
* [OAuth](http://oauth.net/)
* [OAuth 2.0](http://oauth.net/2/)


### Requirements

* Yii 2.0 or above


## Installation

todo