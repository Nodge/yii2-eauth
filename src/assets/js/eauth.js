/*
 * Yii EAuth extension.
 * @author Maxim Zemskov
 * @link http://github.com/Nodge/yii2-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
(function ($) {
	var popup,
		defaults = {
			popup: {
				width: 450,
				height: 380
			}
		};

	function openPopup(options) {
		if (popup != null)
			popup.close();

		var redirect_uri,
			url = redirect_uri = options.url;

		url += url.indexOf('?') >= 0 ? '&' : '?';
		if (url.indexOf('redirect_uri=') === -1)
			url += 'redirect_uri=' + encodeURIComponent(redirect_uri) + '&';
		url += 'js=';

		var centerWidth = (window.screen.width - options.popup.width) / 2,
			centerHeight = (window.screen.height - options.popup.height) / 2;

		popup = window.open(url, "yii_eauth_popup", "width=" + options.popup.width + ",height=" + options.popup.height + ",left=" + centerWidth + ",top=" + centerHeight + ",resizable=yes,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=yes");
		popup.focus();
	}

	$.fn.eauth = function (services) {
		$(this).on('click', '[data-eauth-service]', function (e) {
			e.preventDefault();

			var service = $(this).data('eauthService'),
				options = $.extend({
					url: this.href
				}, defaults, services[service]);

			openPopup(options);
		});
	};
})(jQuery);
