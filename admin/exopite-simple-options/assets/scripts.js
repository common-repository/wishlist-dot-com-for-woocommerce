// WLDC
const wldcfwcDispatchOptionsSavedEvent = new Event( 'wldcfwcOptionsSavedEvent' );

function updateRangeInput(elem)
{
	jQuery( elem ).next().val( jQuery( elem ).val() );
}

function updateInputRange(elem)
{
	jQuery( elem ).prev().val( jQuery( elem ).val() );
}

if (typeof throttle !== "function") {
	// Source: https://gist.github.com/killersean/6742f98122d1207cf3bd
	function throttle(callback, limit, callingEvent)
	{
		var wait = false;
		return function () {
			if (wait && jQuery( window ).scrollTop() > 0) {
				return;
			}
			callback.call( undefined, callingEvent );
			wait = true;
			setTimeout(
				function () {
					wait = false;
				},
				limit
			);
		};
	}
}
if (typeof wldcfwc_submitButtonInViewport !== "function") {
	// Source: https://gist.github.com/killersean/6742f98122d1207cf3bd
	function wldcfwc_submitButtonInViewport()
	{
		// hidden by default
		var save_settings_footer    = document.getElementById( 'wishlist-dot-com-for-woocommerce-save-id' );
		var save_settings_fixed_div = document.getElementById( 'wldcfwc-button-div-fixed-id' );

		// see which tab is selected
		var show_save_button = true;
		var tabs             = document.getElementsByClassName( 'exopite-sof-nav-list-item' );
		for (var i = 0;i < tabs.length;i++) {
			var data_selection = tabs[i].getAttribute( 'data-section' );
			// hide save settings for wldcfwc_dashboard tab
			if ((data_selection == 'wldcfwc_dashboard') && tabs[i].classList.contains( 'active' )) {
				show_save_button = false;
			}
		}

		var html = document.documentElement;
		var rect = save_settings_footer.getBoundingClientRect();
		if ( ! offset) {
			var offset = 15;
		}
		var inView = ! ! rect &&
			rect.bottom >= 0 &&
			rect.right >= 0 &&
			rect.left <= html.clientWidth &&
			rect.top <= (html.clientHeight - offset);

		if (show_save_button && inView) {
			save_settings_fixed_div.classList.add( 'wldcfwc-hide' );
			save_settings_footer.classList.remove( 'wldcfwc-hide' );
		} else if (show_save_button) {
			save_settings_fixed_div.classList.remove( 'wldcfwc-hide' );
			save_settings_footer.classList.remove( 'wldcfwc-hide' );
		}

	}
}
// https://stackoverflow.com/questions/24159478/skip-recursion-in-jquery-find-for-a-selector/24215566?noredirect=1#comment37410122_24215566
jQuery.fn.findExclude = function (selector, mask, result) {
	result = typeof result !== 'undefined' ? result : new jQuery();
	this.children().each(
		function () {
			var thisObject = jQuery( this );
			if (thisObject.is( selector )) {
				result.push( this );
			}
			if ( ! thisObject.is( mask )) {
				thisObject.findExclude( selector, mask, result );
			}
		}
	);
	return result;
};

/**
 * Get url parameter in jQuery
 *
 * @link https://stackoverflow.com/questions/19491336/get-url-parameter-jquery-or-how-to-get-query-string-values-in-js/25359264#25359264
 */ (function ($, window, document, undefined) {
	$.urlParam = function (name) {
		var results = new RegExp( '[\?&]' + name + '=([^&#]*)' ).exec( window.location.href );
		if (results == null) {
			return null;
		} else {
			return decodeURI( results[1] ) || 0;
		}
	};
})( jQuery, window, document );

/**
 * Exopite SOF Media Uploader
 */
; (function ($, window, document, undefined) {

	var pluginName = "exopiteMediaUploader";

	// The actual plugin constructor
	function Plugin(element, options)
	{

		this.element  = element;
		this._name    = pluginName;
		this.$element = $( element );

		this._defaults = $.fn.exopiteMediaUploader.defaults;
		this.options   = $.extend( {}, this._defaults, options );

		this.init();

	}

	Plugin.prototype = {

		init: function () {

			this.bindEvents();

		},

		// Bind events that trigger methods
		bindEvents: function () {
			var plugin = this;

			plugin.$element.find( '.button' ).on(
				'click' + '.' + plugin._name,
				function (event) {
					// this refer to the "[plugin-selector] .button" element
					plugin.openMediaUploader.call( this, event, plugin );
				}
			);

			if (plugin.options.remove !== undefined && plugin.options.input !== undefined && plugin.options.preview !== undefined) {
				plugin.$element.find( plugin.options.remove ).on(
					'click' + '.' + plugin._name,
					function (event) {
						// this refer to the "[plugin-selector] .button" element
						plugin.removePreview.call( this, event, plugin );
					}
				);
			}

		},

		openMediaUploader: function (event, plugin) {

			event.preventDefault();

			/*
			 * Open WordPress Media Uploader
			 *
			 * @link https://rudrastyh.com/wordpress/customizable-media-uploader.html
			 */

			var button          = $( this ),
				parent          = button.closest( '.exopite-sof-media' ),
				isVideo         = parent.hasClass( 'exopite-sof-video' ),
				mediaType       = (isVideo) ? 'video' : 'image',
				custom_uploader = wp.media(
					{
						title: 'Insert image',
						library: {
							// uncomment the next line if you want to attach image to the current post
							// uploadedTo : wp.media.view.settings.post.id,
							type: mediaType
						},
						button: {
							text: 'Use this image' // button label text
						},
						multiple: false // for multiple image selection set to true
					}
				).on(
					'select',
					function () {
							// it also has "open" and "close" events
							var attachment = custom_uploader.state().get( 'selection' ).first().toJSON();

						if (plugin.options.input !== undefined) {
							parent.find( plugin.options.input ).val( attachment.url );
						}
						if ( ! isVideo && plugin.options.preview !== undefined) {
							parent.find( plugin.options.preview ).removeClass( 'hidden' );
							parent.find( 'img' ).attr( { 'src': attachment.url } );
						}
						if (isVideo) {
							parent.find( 'video' ).attr( { 'src': attachment.url } );
						}
					}
				)
					.open();

		},

		removePreview: function (event, plugin) {

			var parent = plugin.$element;

			var previewWrapper = parent.find( plugin.options.preview );
			var previewImg     = parent.find( 'img' );

			if (previewWrapper.css( 'display' ) !== 'none'
				&& previewImg.css( 'display' ) !== 'none'
			) {
				previewWrapper.addClass( 'hidden' );
				previewImg.attr( { 'src': '' } );
			}

			parent.find( plugin.options.input ).val( '' );
		}

	};

	$.fn[pluginName] = function (options) {
		return this.each(
			function () {
				if ( ! $.data( this, "plugin_" + pluginName )) {
					$.data(
						this,
						"plugin_" + pluginName,
						new Plugin( this, options )
					);
				}
			}
		);
	};

})( jQuery, window, document );

/**
 * Exopite SOF Save Options with AJAX
 */
; (function ($, window, document, undefined) {

	var pluginName = "exopiteSaveOptionsAJAX";

	// The actual plugin constructor
	function Plugin(element, options)
	{

		this.element  = element;
		this._name    = pluginName;
		this.$element = $( element );
		this.init();

	}

	Plugin.prototype = {

		init: function () {

			this.bindEvents();

		},

		// Bind events that trigger methods
		bindEvents: function () {
			var plugin = this;

			plugin.$element.find( '.exopite-sof-form-js' ).on(
				'submit' + '.' + plugin._name,
				function (event) {
					plugin.submitOptions.call( this, event );
				}
			);

			/**
			 * Save on CRTL+S
			 *
			 * @link https://stackoverflow.com/questions/93695/best-cross-browser-method-to-capture-ctrls-with-jquery/14180949#14180949
			 */
			$( window ).on(
				'keydown' + '.' + plugin._name,
				function (event) {

					if (plugin.$element.find( '.exopite-sof-form-js' ).length) {
						if (event.ctrlKey || event.metaKey) {
							switch (String.fromCharCode( event.which ).toLowerCase()) {
								case 's':
									event.preventDefault();
									var $form = plugin.$element.find( '.exopite-sof-form-js' );
									plugin.submitOptions.call( $form, event );
								break;
							}
						}
					}
				}
			);

			// WLDC changed to inViewport
			$( window ).on( 'scroll' + '.' + plugin._name, throttle( wldcfwc_submitButtonInViewport ) );

		},

		// Unbind events that trigger methods
		unbindEvents: function () {
			this.$element.off( '.' + this._name );
		},

		checkFixed: function () {

			var footerWidth = $( '.exopite-sof-form-js' ).outerWidth();
			var bottom      = 0;

			// WLDC commenting out                 && ($(window).scrollTop() + $(window).height() < $(document).height() - 100)
			if (($( window ).scrollTop() > ($( '.exopite-sof-header-js' ).position().top + $( '.exopite-sof-header-js' ).outerHeight( true )))
			) {
				bottom = '0';
			} else {
				bottom = '-' + $( '.exopite-sof-footer-js' ).outerHeight() + 'px';
			}

			$( '.exopite-sof-footer-js' ).outerWidth( footerWidth );
			$( '.exopite-sof-footer-js' ).css(
				{
					bottom: bottom,
				}
			);

		},

		/**
		 * https://thoughtbot.com/blog/ridiculously-simple-ajax-uploads-with-formdata
		 * https://stackoverflow.com/questions/17066875/how-to-inspect-formdata
		 * https://developer.mozilla.org/en-US/docs/Web/API/FormData/FormData
		 * https://developer.mozilla.org/en-US/docs/Web/API/FormData
		 * https://stackoverflow.com/questions/2019608/pass-entire-form-as-data-in-jquery-ajax-function
		 * https://stackoverflow.com/questions/33487360/formdata-and-checkboxes
		 */
		submitOptions: function (event) {

			event.preventDefault();
			var saveButtonString    = $( this ).data( 'save' );
			var savedButtonString   = $( this ).data( 'saved' );
			var $submitButtons      = $( this ).find( '.exopite-sof-submit-button-js' );
			var currentButtonString = $submitButtons.val();
			var $ajaxMessage        = $( this ).find( '.exopite-sof-ajax-message' );

			var quiet_save = false;
			if($submitButtons.attr('quiet_save')){
				quiet_save = true;
			}

			if(!quiet_save){
				$submitButtons.val( saveButtonString ).attr( 'disabled', true );
			}

			if (typeof tinyMCE !== 'undefined') {
				tinyMCE.triggerSave();
			}

			var formElement = $( this )[0];
			var formData    = new FormData( formElement );

			var formName = $( '.exopite-sof-form-js' ).attr( 'name' );

			/**
			 * 2.) Via ajaxSubmit
			 */
			var $that = $( this );
			$( this ).ajaxSubmit(
				{
					beforeSubmit: function (arr, $form, options) {
						// The array of form data takes the following form:
						// [ { name: 'username', value: 'jresig' }, { name: 'password', value: 'secret' } ]
						// https://jsonformatter.curiousconcept.com/

						$that.find( '[name]' ).not( ':disabled' ).each(
							function (index, el) {
								if ($( el ).prop( 'nodeName' ) == 'INPUT' && $( el ).attr( 'type' ) == 'checkbox' && ! $( el ).is( ":checked" ) && ! $( el ).attr( 'name' ).endsWith( '[]' )) {
									// not checked checkbox
									var element = {
										"name": $( el ).attr( 'name' ),
										"value": "no",
										"type": "checkbox",
										// "required":false
									};
									arr.push( element );
								}
								if ($( el ).prop( 'nodeName' ) == 'SELECT' && $( el ).val() == null) {
									// multiselect is empty
									var element = {
										"name": $( el ).attr( 'name' ),
										"value": "",
										"type": "select",
										// "required":false
									};
									arr.push( element );
								}
							}
						);

						// return false to cancel submit
					},
					success: function () {
						$submitButtons.val( currentButtonString ).attr( 'disabled', false );

						if(!quiet_save){
							$ajaxMessage.html( savedButtonString ).addClass( 'success show' );
						}

						// WLDC
						var wldcfwc_exopite_sof_form_js = document.querySelector( '.exopite-sof-form-js' );
						if (wldcfwc_exopite_sof_form_js) {
							wldcfwc_exopite_sof_form_js.dispatchEvent( wldcfwcDispatchOptionsSavedEvent );
						}

						$submitButtons.blur();
						setTimeout(
							function () {
								// $ajaxMessage.fadeOut( 400 );
								$ajaxMessage.removeClass( 'show' );

								$submitButtons.removeAttr('quiet_save');

							},
							3000
						);
					},

					error: function (data) {
						$submitButtons.val( currentButtonString ).attr( 'disabled', false );
						$ajaxMessage.html( 'Error. Try refreshing page.' ).addClass( 'error show' );
					},
				}
			);

			return false;

		}

	};

	$.fn[pluginName] = function (options) {
		return this.each(
			function () {
				if ( ! $.data( this, "plugin_" + pluginName )) {
					$.data(
						this,
						"plugin_" + pluginName,
						new Plugin( this, options )
					);
				}
			}
		);
	};

})( jQuery, window, document );

/**
 * Exopite SOF Options Navigation
 */
; (function ($, window, document, undefined) {

	/**
	 * A jQuery Plugin Boilerplate
	 *
	 * https://github.com/johndugan/jquery-plugin-boilerplate/blob/master/jquery.plugin-boilerplate.js
	 * https://john-dugan.com/jquery-plugin-boilerplate-explained/
	 */

	var pluginName = "exopiteOptionsNavigation";

	// The actual plugin constructor
	function Plugin(element, options)
	{

		this.element  = element;
		this._name    = pluginName;
		this.$element = $( element );
		this.init();

	}

	function keyValUpdateQueryStringParam(url,key,value)
	{
		// encode prior to calling function?
		if (value && typeof value === 'string') {
			if (value.indexOf( '%' ) == -1) {
				value = encodeURI( value );
			}
		}

		var url_a         = parseUrlCommon( url );
		var baseUrl       = url_a['base'];
		var qs_parameters = url_a['qs_parameters'];
		var hashIndex     = url_a['hashIndex'];

		var outPara = {};
		for (k in qs_parameters) {
			var ekey   = k;
			var evalue = qs_parameters[k];
			// add this to array of exisiting params if it doesn't match param passed
			if (ekey != key) {
				outPara[ekey] = evalue;
			}
		}

		if (value !== undefined && value !== null && value !== 'null' && value !== '') {
			// add new param to existing params
			outPara[key] = value;
		} else {
			// remove param from url if it's null
			delete outPara[key];
		}
		parameters = [];
		for (var k in outPara) {
			parameters.push( k + '=' + outPara[k] );
		}

		var finalUrl = baseUrl;

		if (parameters.length > 0) {
			finalUrl += '?' + parameters.join( '&' );
		}

		var output_url = finalUrl + url.substring( hashIndex );

		return output_url;
	}
	function parseUrlCommon(url)
	{
		var hashIndex = url.indexOf( "#" ) | 0;
		if (hashIndex === -1) {
			hashIndex = url.length | 0;
		}
		var urls = url.substring( 0, hashIndex ).split( '?' );

		var base       = urls[0];
		var qs         = '';
		var parameters = '';
		var outPara    = {};
		if (urls.length > 1) {
			var qs     = urls[1];
			parameters = urls[1];
		}
		if (parameters !== '') {
			// these are existing params
			parameters = parameters.split( '&' );
			for (k in parameters) {
				var keyVal = parameters[k];
				keyVal     = keyVal.split( '=' );
				var ekey   = keyVal[0];
				var evalue = '';
				if (keyVal.length > 1) {
					evalue = keyVal[1];
				}
				outPara[ekey] = evalue;
			}
		}
		return {'base':base,'qs':qs,'qs_parameters':outPara,'hashIndex':hashIndex}
	}
	function getUrlParameter(param)
	{
		param       = param.replace( /[\[]/, '\\[' ).replace( /[\]]/, '\\]' );
		var regex   = new RegExp( '[\\?&]' + param + '=([^&#]*)' );
		var results = regex.exec( location.search );
		return results === null ? '' : decodeURIComponent( results[1].replace( /\+/g, ' ' ) );
	};

	Plugin.prototype = {

		init: function () {

			this.bindEvents();

		},

		// Bind events that trigger methods
		bindEvents: function () {
			var plugin = this;

			plugin.onLoad.call( plugin );

			plugin.$element.find( '.exopite-sof-nav-list-item' ).on(
				'click' + '.' + plugin._name,
				function () {

					plugin.changeTab.call( plugin, $( this ) );

				}
			);

			plugin.$element.find( '.exopite-sof-nav-list-parent-item > .exopite-sof-nav-list-item-title' ).on(
				'click' + '.' + plugin._name,
				function () {

					plugin.toggleSubMenu.call( plugin, $( this ) );

				}
			);

		},

		// Unbind events that trigger methods
		unbindEvents: function () {
			this.$element.off( '.' + this._name );
		},

		toggleSubMenu: function (button) {
			// var $parent = button;
			var $parent = button.parents( '.exopite-sof-nav-list-parent-item' );
			$parent.toggleClass( 'active' ).find( 'ul' ).slideToggle( 200 );
		},
		changeTab: function (button) {

			var tab_section = button.data( 'section' );

			if ( ! button.hasClass( 'active' )) {
				var section = '.exopite-sof-section-' + button.data('section');
				this.$element.find('.exopite-sof-nav-list-item.active').removeClass('active');
				this.$element.find('.exopite-sof-section').addClass('hide');
				this.$element.find(section).removeClass('hide');
				button.addClass( 'active' );
			}

		},

		onLoad: function () {
			var plugin = this;

			/**
			 * "Sanitize" URL
			 *
			 * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/encodeURIComponent
			 */
			var URLSection = encodeURIComponent( $.urlParam( 'section' ) );

			// If section doesn't exist, then return
			if (URLSection !== 'null' && ! plugin.$element.find( '.exopite-sof-section-' + URLSection ).length) {
				return false;
			}

			// WLDC adding default active tab
			if (URLSection !== 'null') {
				var navList = plugin.$element.find( '.exopite-sof-nav-list-item' );
				plugin.$element.find( '.exopite-sof-section' ).addClass( 'hide' );
				plugin.$element.find( '.exopite-sof-section-' + URLSection ).removeClass( 'hide' );
				navList.removeClass( 'active' );
				navList.each(
					function (index, el) {
						var section = $( el ).data( 'section' );
						if (section == URLSection) {
							$( el ).addClass( 'active' );
						}
					}
				);
			} else {
				var firstNavItem = plugin.$element.find( '.exopite-sof-nav-list-item' ).first();
				if (firstNavItem) {
					firstNavItem.addClass( 'active' );
				}
			}
		},

	};

	$.fn[pluginName] = function (options) {
		return this.each(
			function () {
				if ( ! $.data( this, "plugin_" + pluginName )) {
					$.data(
						this,
						"plugin_" + pluginName,
						new Plugin( this, options )
					);
				}
			}
		);
	};

})( jQuery, window, document );

/**
 * Init
 */
; (function ($) {
	"use strict";

	$( document ).ready(
		function () {

			$( '.exopite-sof-wrapper-menu' ).exopiteSaveOptionsAJAX();
			$( '.exopite-sof-content-js' ).exopiteOptionsNavigation();
			$( '.exopite-sof-media' ).exopiteMediaUploader(
				{
					input: 'input',
					preview: '.exopite-sof-image-preview',
					remove: '.exopite-sof-image-remove'
				}
			);

		}
	);

}(jQuery));
