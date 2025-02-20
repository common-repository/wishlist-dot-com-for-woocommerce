function wldcfwc_init_admin(){
	// When user clicks "Review Connection"
	var popwin_buttons = document.querySelectorAll( '.wldcfwc-popwin-button-js' );
	popwin_buttons.forEach(
		function (button) {
			button.addEventListener(
				'click',
				function (event) {
					wldcfwc_open_popwin( event )
				}
			);
		}
	);
	function wldcfwc_popwinWH(width) {
		var window_wh = [480, 550]
		try {
			var minWindowWidth = 480;
			var maxWindowWidth = 850;
			if (width) {
				maxWindowWidth = width;
			}
			var windowWidth = maxWindowWidth;
			var screenWidth = parseInt( document.documentElement.clientWidth );
			if (screenWidth < maxWindowWidth) {
				windowWidth = parseInt( screenWidth * .90 );
			} else if (windowWidth < minWindowWidth) {
				windowWidth = minWindowWidth;
			}
			var minWindowHeight = 480;
			var maxWindowHeight = 790;
			var windowHeight    = maxWindowHeight;
			var screenHeight    = parseInt( document.documentElement.clientHeight );
			if (screenHeight < maxWindowHeight) {
				windowHeight = parseInt( screenHeight * .75 );
			} else if (windowHeight < minWindowHeight) {
				windowHeight = minWindowHeight;
			}
			var window_wh = [windowWidth, windowHeight]
		} catch (e) {
		}
		return window_wh
	}
	function wldcfwc_open_popwin(event){
		event.preventDefault();
		var button = event.target;
		var orig_btn_txt = button.innerText;
		button.setAttribute( 'data-orig_btn_txt', orig_btn_txt );
		button.setAttribute( 'disabled', true );
		button.innerText = 'Opening...';

		var button_mode = button.getAttribute( 'data-button-mode' );

		if(button_mode == 'get_support'){
			wldcfwc_do_popwin(null, button);
		}else{
			//use promise to get once_auth_token and pass it to wldcfwc_do_popwin()
			wldcfwc_get_once_auth_token_rec_ajax().then(response => {
				if(response.message.data.status == 'success' && response.message.data.data && response.message.data.data.once_auth_token){
					once_auth_token = response.message.data.data.once_auth_token;

					var button_mode = button.getAttribute( 'data-button-mode' );
					if(button_mode && button_mode == 'preview_email_template'){
						var error_prompt = document.getElementById('wldcfwc-preview-email-error');
					}else if(button_mode && button_mode == 'wldcom_plugin_settings'){
						var error_prompt = document.getElementById('wldcfwc-wldcom-plugin-settings-error');
					}else{
						var error_prompt = document.getElementById('wldcfwc-dashboard-plugin-error');
					}
					error_prompt.classList.add( 'wldcfwc-hide' );
					wldcfwc_do_popwin(once_auth_token, button);
				}else{
					button.removeAttribute('disabled');
					button.innerText = orig_btn_txt;
					var button_mode = button.getAttribute( 'data-button-mode' );
					if(button_mode && button_mode == 'preview_email_template'){
						var error_prompt = document.getElementById('wldcfwc-preview-email-error');
					}else if(button_mode && button_mode == 'wldcom_plugin_settings'){
						var error_prompt = document.getElementById('wldcfwc-wldcom-plugin-settings-error');
					}else{
						var error_prompt = document.getElementById('wldcfwc-dashboard-plugin-error');
					}
					thisFadeIn( error_prompt,'wldcfwc-hide' );
				}
			});
		}
	}
	function wldcfwc_do_popwin(once_auth_token, button){
		var button_mode = button.getAttribute( 'data-button-mode' );
		var orig_btn_txt = button.getAttribute( 'data-orig_btn_txt' );
		button.removeAttribute('disabled');
		button.innerText = orig_btn_txt;
		if (button_mode == 'dashboard') {
			var width      = 1800;
			var window_wh = wldcfwc_popwinWH( width );
		} else {
			var window_wh = wldcfwc_popwinWH();
		}
		var windowWidth  = window_wh[0];
		var windowHeight = window_wh[1];
		var client_width = parseInt( document.documentElement.clientWidth );
		var leftPos      = client_width - windowWidth / 2;

		var store_shop_url = createUrlQsParamJS( 'store_shop_url',wldcfwc_store_url );

		// default api domain when there's no store_uuid as yet
		var popwin_api_domain = 'a' + wldcfwc_env + 'pw.wishlist.com';

		if (wldcfwc_store_uuid.length > 0) {
			var store_uuid_connected = wldcfwc_store_uuid;
		} else {
			var store_uuid_connected = '';
		}
		var open_window_name = "WooComWishListPopWindow";
		var api_action       = 'dashboard';
		if (button_mode == 'dashboard') {
			api_action = 'dashboard';
		} else if (button_mode == 'wldcom_plugin_settings') {
			api_action = 'wldcom_plugin_settings';
		} else if (button_mode == 'preview_email_template') {
			api_action = 'preview_email_template';
		} else if (button_mode == 'get_support') {
			api_action = 'get_support';
		}
		// avoid cache
		var rand = Math.floor( Math.random() * 1000 );

		if(once_auth_token){
			var once_auth_token_string = '&onceauthtoken=' +once_auth_token;
		}else{
			var once_auth_token_string = '';
		}
		var open_url = 'https://' + popwin_api_domain + '/wooCommApi/?api_action=' + api_action + once_auth_token_string + '&plugin_code=w&store_uuid=' + store_uuid_connected + '&store_shop_url=' + store_shop_url + '&rand=' + rand;

		window.open(
			open_url,
			open_window_name,
			"location=yes,menubar=yes,width=" + windowWidth + ",height=" + windowHeight + ",top=50,left=" + leftPos
		);

	}
	function wldcfwc_get_once_auth_token_rec_ajax() {
		const data = new URLSearchParams();
		data.append('action', 'wldcfwc_get_once_auth_token_rec_ajax');
		data.append('_wpnonce', wldcfwc_object_admin.nonce.wldcfwc_get_once_auth_token_rec_ajax);

		return fetch(ajaxurl, {
			method: 'POST',
			body: data,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		})
			.then(response => response.json())
			.then(data => {
				// Check if message.data.status exists; if not, set a default value
				if (!data.message || !data.message.data || typeof data.message.data.status === 'undefined') {
					// Set a default status value, e.g., 'unknown' or any suitable default
					data.message = data.message || {};
					data.message.data = data.message.date || {};
					data.message.data.status = 'error_fetching';
				}
				return data;
			})
			.catch(error => {
				console.error('Error fetching auth token:', error);
				return { message: { data: { status: 'error_fetching' } } };
			});
	}
	function wldcfwc_get_theme_primary_colors_rec_ajax() {
		const data = new URLSearchParams();
		data.append('action', 'wldcfwc_get_theme_primary_colors_rec_ajax');
		data.append('_wpnonce', wldcfwc_object_admin.nonce.wldcfwc_get_theme_primary_colors_rec_ajax);

		return fetch(ajaxurl, {
			method: 'POST',
			body: data,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		})
			.then(response => response.json())
			.then(data => {
				// Check if message.data.status exists; if not, set a default value
				if (!data.message || !data.message.data || typeof data.message.data.status === 'undefined') {
					// Set a default status value, e.g., 'unknown' or any suitable default
					data.message = data.message || {};
					data.message.data = data.message.date || {};
					data.message.data.status = 'error_fetching';
				}
				return data;
			})
			.catch(error => {
				console.error('Error fetching auth token:', error);
				return { message: { data: { status: 'error_fetching' } } };
			});
	}

	var theme_primary_colors_poplulated = false;
	function getThemePrimaryColorsAndPopulate(){
		//use promise to get once_auth_token and pass it to wldcfwc_do_popwin()
		if(!theme_primary_colors_poplulated){

			wldcfwc_get_theme_primary_colors_rec_ajax().then(response => {
				if(response.status == 'success' && response.message.theme_primary_colors){
					populateThemeColorFields(response.message.theme_primary_colors);
				}
			});
		}
	}

	function populateThemeColorFields(theme_primary_colors) {

		var anyKeyPopulated = false; // Track if at least one key is populated

		var button_suffix = ['share_button', 'primary_button', 'danger_button'];
		var field_names = ['text_color', 'background_color', 'border_color'];
		var button_cat;

		for (var i = 0; i < button_suffix.length; i++) {
			for (var k = 0; k < field_names.length; k++) {

				if (button_suffix[i] === 'share_button' || button_suffix[i] === 'primary_button') {
					button_cat = 'primary';
				} else {
					button_cat = 'danger';
				}

				if (button_cat === 'primary' || button_cat === 'danger') {
					var base_name = 'wlcom_wishlist_button_' + field_names[k] + '__' + button_suffix[i];
					if (theme_primary_colors.hasOwnProperty(base_name)) {
						var color = theme_primary_colors[base_name];
						var selector = 'input[name="wishlist-dot-com-for-woocommerce[' + base_name + ']"]';
						var field = document.querySelector(selector);
						if (field) {
							if (color && color.length > 0) {
								update_color_picker_field(field, color);
								anyKeyPopulated = true; // Mark true if any field is populated
							}
						}
					}
					var hover_name = 'wlcom_wishlist_button_' + field_names[k] + '_hover__' + button_suffix[i];
					if (theme_primary_colors.hasOwnProperty(hover_name)) {
						var hover_color = theme_primary_colors[hover_name];
						selector = 'input[name="wishlist-dot-com-for-woocommerce[' + hover_name + ']"]';
						field = document.querySelector(selector);
						if (field) {
							if (hover_color && hover_color.length > 0) {
								update_color_picker_field(field, hover_color);
								anyKeyPopulated = true; // Mark true if any field is populated
							}
						}
					}
				}
			}
		}

		// Check other individual fields
		var colorFields = [
			{ key: 'wlcom_template_store_name__text_color', selector: 'input[name="wishlist-dot-com-for-woocommerce[wlcom_template_store_name__text_color]"]' },
			{ key: 'wlcom_header_template_menu_item__text_color', selector: 'input[name="wishlist-dot-com-for-woocommerce[wlcom_header_template_menu_item__text_color]"]' },
			{ key: 'wlcom_header_template_menu_item__text_color_hover', selector: 'input[name="wishlist-dot-com-for-woocommerce[wlcom_header_template_menu_item__text_color_hover]"]' },
			{ key: 'wlcom_header_template_menu_item__text_font_size', selector: 'input[name="wishlist-dot-com-for-woocommerce[wlcom_header_template_menu_item__text_font_size]"]' },
			{ key: 'wlcom_header_template_menu_item__text_decoration', selector: 'input[name="wishlist-dot-com-for-woocommerce[wlcom_header_template_menu_item__text_decoration]"]' },
			{ key: 'wlcom_text_color', selector: 'input[name="wishlist-dot-com-for-woocommerce[wlcom_text_color]"]' }
		];

		colorFields.forEach(fieldData => {
			var color = theme_primary_colors[fieldData.key];
			var field = document.querySelector(fieldData.selector);
			if (field && color && color.length > 0) {
				if (fieldData.key.includes('color')) {
					update_color_picker_field(field, color);
				} else {
					field.value = color;
				}
				anyKeyPopulated = true; // Mark true if any field is populated
			}
		});

		// Only set hidden field and flag if at least one key is populated
		if (anyKeyPopulated) {
			createHiddenField("js_theme_colors_merged_saved", 'yes_str');
			theme_primary_colors_poplulated = true;
			saveForm('quiet');
		}
	}

	function addTabFocusListener(){
		window.addEventListener('focus', function() {
			getThemePrimaryColorsAndPopulate();
		});
	}
	if(js_theme_colors_merged_saved && js_theme_colors_merged_saved != 'yes_str'){
		addTabFocusListener();
		getThemePrimaryColorsAndPopulate();
	}
	function createUrlQsParamJS(param_name,param_val){
		param_val = param_val.replace( '?','__qm__' );
		param_val = param_val.replace( '&','__amp__' );
		param_val = param_val.replace( '=','__eq__' );
		param_val = param_val.replace( '%','__per__' );
		param_val = param_val + '__' + param_name + '__end';
		return param_val;
	};
	function getUrlQsParamJS(param_name,qs){
		var param_val = qs;
		const fs      = param_name + "=";
		const fe      = param_name + "__end";
		var startPos  = qs.indexOf( fs ) + fs.length;
		var endPos    = qs.indexOf( fe, startPos );
		if (endPos == -1) {
			endPos = qs.length;
		}
		if (startPos > fs.length - 1) {
			param_val = qs.substring( startPos, endPos );
		}
		param_val = param_val.replace( '__qm__','?' );
		param_val = param_val.replace( '__amp__','&' );
		param_val = param_val.replace( '__eq__','=' );
		param_val = param_val.replace( '__per__','%' );

		return param_val;
	};
	// listen for changed form and warn if they haven't saved settings prior to leaving page
	var formHasChanged = false;
	var formSaved      = false;
	function addFormChangeListener(){
		var form = document.forms['wishlist-dot-com-for-woocommerce'];
		// Listen for changes in the form
		if (form) {
			form.addEventListener(
				'change',
				function () {
					formHasChanged = true;
				}
			);

			window.onbeforeunload = function (event) {
				if (formHasChanged && ! formSaved) {
					// wordpress seems to change the message
					var message       = 'You have unsaved changes. Are you sure you want to leave?';
					event.returnValue = message; // Standard for most browsers
					return message; // For older browsers
				}
			};

		}
	}
	addFormChangeListener();

	function keyValUpdateQueryStringParam(url,key,value){
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
	function parseUrlCommon(url){
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
	function getUrlParameter(param) {
		param = param.replace( /[\[]/, '\\[' ).replace( /[\]]/, '\\]' );
		var regex = new RegExp( '[\\?&]' + param + '=([^&#]*)' );
		var results = regex.exec( location.search );
		return results === null ? '' : decodeURIComponent( results[1].replace( /\+/g, ' ' ) );
	};
	function update_color_picker_field(colorPickerInput, color){

		// Update the value of the color picker input
		colorPickerInput.value = color;
		// Trigger the change event manually using jQuery, since wpColorPicker relies on jQuery
		if (typeof jQuery !== 'undefined' && jQuery.fn.wpColorPicker) {
			//note that the new input val isn't viewable in chrome debug
			jQuery(colorPickerInput).wpColorPicker('color', color);
			// Force refresh by triggering change events
			jQuery(colorPickerInput).trigger('input').trigger('change');
		} else {
			// Fallback if jQuery is not available
			colorPickerInput.dispatchEvent(new Event('change', { bubbles: true }));
		}
	}

	// WLDC
	var wldcfwc_exopite_sof_form_js = document.querySelector( '.exopite-sof-form-js' );
	if (wldcfwc_exopite_sof_form_js) {
		wldcfwc_exopite_sof_form_js.addEventListener(
			'wldcfwcOptionsSavedEvent',
			function (event) {

				formSaved = true;

				wldcfwc_toggleAdminSections();

				// doing this asychronously
				if (typeof jQuery.fn.wldcfwc_trigger_post_options_to_wlcom_rec_ajax === 'function') {
					jQuery.fn.wldcfwc_trigger_post_options_to_wlcom_rec_ajax();
				}
			}
		);
	}
	function wldcfwc_toggleAdminSections() {
		// called on load
		checkConnection();
		checkDisplayStatus();
	}
	wldcfwc_toggleAdminSections();

	function createHiddenField(name,value){
		var wl_form = document.forms['wishlist-dot-com-for-woocommerce'];
		if (wl_form) {
			var hiddenField = document.querySelector( 'input[name="wishlist-dot-com-for-woocommerce[' + name + ']"]' );
			if ( ! hiddenField) {
				// Create a hidden input element
				var hiddenField  = document.createElement( 'input' );
				hiddenField.type = 'hidden';
				hiddenField.name = 'wishlist-dot-com-for-woocommerce[' + name + ']';

				wl_form.appendChild( hiddenField );
			}
			hiddenField.value = value;
		}
	}
	function checkConnection(mode){
		// called on load
		var connect_to_wldcom_section   = document.querySelectorAll( ".wldcfwc-connect-store" );
		var connected_to_wldcom_section = document.querySelectorAll( ".wldcfwc-connected-store" );
		var connected_status_prompt = document.getElementById('connected_status_prompt');

		if (wldcfwc_valid_api_key != 'yes_str' || mode == 'connect') {
			// show
			connect_to_wldcom_section.forEach(
				function (elm) {
					thisFadeIn( elm,'wldcfwc-hide' );
					elm.classList.remove( 'wldcfwc-hide' );
				}
			);
			// hide
			connected_to_wldcom_section.forEach(
				function (elm) {
					elm.classList.add( 'wldcfwc-hide' );
				}
			);
			//show
			connected_status_prompt.classList.remove('wldcfwc-hide');
		} else {
			// hide
			connect_to_wldcom_section.forEach(
				function (elm) {
					elm.classList.add( 'wldcfwc-hide' );
				}
			);
			// show
			connected_to_wldcom_section.forEach(
				function (elm) {
					thisFadeIn( elm,'wldcfwc-hide' )
					elm.classList.remove( 'wldcfwc-hide' );
				}
			);
			//hide
			connected_status_prompt.classList.add('wldcfwc-hide');
		}
	}
	checkConnection();

	function toggle_use_email_template(init){
		// wldcfwc-hide
		// wldcfwc-show_hide_email_custom_template__
		// get radios
		var use_email_telmplate_radios = document.querySelectorAll( '.wldcfwc-use_email_template' );
		for (var i = 0;i < use_email_telmplate_radios.length;i++) {
			var cur_suffix  = '';
			var cur_checked = false;
			var cur_val     = '';
			var radios      = use_email_telmplate_radios[i].querySelectorAll( 'input[type="radio"]' );
			for (var j = 0;j < radios.length;j++) {
				var radio         = radios[j];
				var radio_name    = radio.name;
				var radio_val     = radio.value;
				var radio_checked = radio.checked;

				var suffix = '';
				var match  = radio_name.match( /[^_]+$/ );
				if (match && match[0]) {
					suffix = match[0];
				}

				var suffix_a = radio_name.split( '__' );
				suffix       = suffix_a[suffix_a.length - 1];
				suffix       = suffix.replace( ']','' );

				if (suffix.length > 0) {
					cur_suffix = suffix;
					if (radio.checked) {
						cur_val     = radio_val;
						cur_checked = radio.checked;
					}

					if (init) {
						radio.addEventListener(
							'change',
							function (event) {
								toggle_use_email_template( false );
							}
						);
					}
				}
			}
			if (cur_suffix.length) {
				var custom_sections = document.querySelectorAll( '.wldcfwc-show_hide_email_custom_template__' + suffix );
				for (var k = 0;k < custom_sections.length;k++) {
					if (suffix != 'email_template') {
						if (cur_val == 'custom_email') {
							// custom_sections[k].classList.remove('wldcfwc-hide');
							thisFadeIn( custom_sections[k],'wldcfwc-hide' )
						} else {
							custom_sections[k].classList.add( 'wldcfwc-hide' );
						}
					} else {
						thisFadeIn( custom_sections[k],'wldcfwc-hide' )
					}
				}
			}
		}
	}
	toggle_use_email_template( true );

	//warn when the display status is not live
	function checkDisplayStatusListener(){
		const statusRadioButtons = document.querySelectorAll('input[name="wishlist-dot-com-for-woocommerce[wlcom_plgn_plugin_display_status]"]');
		statusRadioButtons.forEach(function(radio) {
			radio.addEventListener('change', checkDisplayStatus);
		});
	}
	checkDisplayStatusListener();

	//warn when the display status is not live
	function checkDisplayStatus() {
		const statusRadioButtons = document.querySelectorAll('input[name="wishlist-dot-com-for-woocommerce[wlcom_plgn_plugin_display_status]"]');
		const warningDiv = document.getElementById('display_status_prompt');
		const selectedValue = document.querySelector('input[name="wishlist-dot-com-for-woocommerce[wlcom_plgn_plugin_display_status]"]:checked').value;
		if (selectedValue !== 'status_live') {
			thisFadeIn( warningDiv,'wldcfwc-hide' )
		} else {
			warningDiv.classList.add( 'wldcfwc-hide' );
		}
	}

	//warning prompt link that opens start tab
	const select_display_status_link = document.querySelectorAll('.change_display_status_link');
	select_display_status_link.forEach(function(elm) {
		elm.addEventListener('click',function(){
			var wldcfwc_general_settings_start_tab = document.querySelector('[data-section="wldcfwc_general_settings_start"]');
			if (wldcfwc_general_settings_start_tab) {
				wldcfwc_general_settings_start_tab.click();
			}
		});
	});

	//copy wlcom_admin_email to empty wlcom_email_conf__reply_to_address__ addresses
	const adminEmailField = document.querySelector('input[name="wishlist-dot-com-for-woocommerce[wlcom_admin_email]"]');
	if (adminEmailField) {
		adminEmailField.addEventListener('blur', function() {
			const reply_toEmailFields = document.querySelectorAll('input[name^="wishlist-dot-com-for-woocommerce[wlcom_email_conf__reply_to_address__"]');
			const adminEmailValue = adminEmailField.value;
			reply_toEmailFields.forEach(function(field) {
				if (!field.value) { // Only update if the field is empty
					field.value = adminEmailValue;
				}
			});
		});
	}

	function thisFadeIn(obj,remove_css_class){
		if (obj.classList.contains( remove_css_class )) {
			var opacity_var   = 0;
			obj.style.opacity = '0%';
			obj.classList.remove( remove_css_class );
			var max_cnt    = 50,cnt = 0;
			var fadeEffect = setInterval(
				function () {
					if (opacity_var < 100 && cnt < max_cnt) {
						opacity_var       = opacity_var + 10;
						obj.style.opacity = opacity_var + '%';
					} else {
						obj.style.opacity = '100%';
						clearInterval( fadeEffect );
					}
					cnt++;
				},
				20
			);
		}
	}

	jQuery( document ).ready(
		function ($) {
			$.fn.wldcfwc_trigger_post_options_to_wlcom_rec_ajax = function () {
				$.ajax(
					{
						url: ajaxurl, // WordPress AJAX URL
						type: 'POST',
						data: {
							action: 'wldcfwc_post_options_to_wlcom_rec_ajax', // Action to trigger on the server,
							_wpnonce: wldcfwc_object_admin.nonce.wldcfwc_post_options_to_wlcom_rec_ajax
						},
						success: function (response) {
						},
					}
				);
			}
			$.fn.wldcfwc_trigger_get_wlcom_api_key_rec_ajax = function (button) {
				button.prop('disabled', true);
				button.text('Connecting...');
				$.ajax(
					{
						url: ajaxurl, // WordPress AJAX URL
						type: 'POST',
						data: {
							action: 'wldcfwc_get_wlcom_api_key_rec_ajax', // Action to trigger on the server,
							_wpnonce: wldcfwc_object_admin.nonce.wldcfwc_get_wlcom_api_key_rec_ajax
						},
						success: function (response) {
							$.fn.wldcfwc_get_api_key_response(response);
						},
						error: function (response) {
							$.fn.wldcfwc_get_api_key_response(response);
						},
					}
				);
			}
			$('#wldcfwc-connect-to-wldcom-js').on('click',function (event) {
				event.preventDefault();
				$.fn.wldcfwc_trigger_get_wlcom_api_key_rec_ajax($(this));
			});
			$.fn.wldcfwc_get_api_key_response = function (response) {
				var button = $('#wldcfwc-connect-to-wldcom-js');
				button.prop('disabled', false);
				button.text('Connect');
				if(response && response.message && response.message.data && response.message.data.status !== undefined && response.message.data.status == 'success'){
					wldcfwc_valid_api_key = 'yes_str';

					var error_prompt = document.getElementById('wldcfwc-connect-store-error');
					if(error_prompt){
						error_prompt.classList.add('wldcfwc-hide');
					}
				}else {
					var error_prompt = document.getElementById('wldcfwc-connect-store-error');
					thisFadeIn(error_prompt, 'wldcfwc-hide');
				}
				checkConnection();
			}
			$.fn.wldcfwc_trigger_delete_wlcom_api_key_rec_ajax = function (button) {
				button.addClass('exopite-sof-disabled-link');
				button.text('Deleting connection...');
				$.ajax(
					{
						url: ajaxurl, // WordPress AJAX URL
						type: 'POST',
						data: {
							action: 'wldcfwc_delete_wlcom_api_key_rec_ajax', // Action to trigger on the server,
							_wpnonce: wldcfwc_object_admin.nonce.wldcfwc_delete_wlcom_api_key_rec_ajax
						},
						success: function (response) {
							$.fn.wldcfwc_delete_api_key_response(response);
						},
						error: function (response) {
							$.fn.wldcfwc_delete_api_key_response(response);
						},
					}
				);
			}
			$('#wldcfwc-delete-connection-to-wldcom-js').on('click',function (event) {
				/*var delete_link = $('#wldcfwc-delete-connection-to-wldcom-js');
				delete_link.hide();*/
				event.preventDefault();
				var warning = $('#wldcfwc-delete-connection-confirmation');
				warning.hide();
				warning.removeClass('wldcfwc-hide');
				warning.fadeIn();
			});
			$('#wldcfwc-cancel-delete-connection-to-wldcom-js').on('click',function (event) {
				event.preventDefault();
				var warning = $('#wldcfwc-delete-connection-confirmation');
				warning.fadeOut();
				warning.addClass('wldcfwc-hide');
				/*var delete_link = $('#wldcfwc-delete-connection-to-wldcom-js');
				delete_link.show();*/
			});
			$('#wldcfwc-confirm-delete-connection-to-wldcom-js').on('click',function (event) {
				event.preventDefault();
				$.fn.wldcfwc_trigger_delete_wlcom_api_key_rec_ajax($(this));
			});
			$.fn.wldcfwc_delete_api_key_response = function (response) {
				var button = $('#wldcfwc-confirm-delete-connection-to-wldcom-js');
				button.removeClass('exopite-sof-disabled-link');
				button.text('Delete connection');

				wldcfwc_valid_api_key = 'no_str';
				var warning = $('#wldcfwc-delete-connection-confirmation');
				warning.fadeOut();
				warning.addClass('wldcfwc-hide');

				checkConnection();
			}
			// admin sub tabs
			$( '.wldcfwc-sub-nav' ).on(
				'click',
				function (event) {
					// target
					var content_id = $( this ).attr( 'data-content-id' );
					$( this ).parent( '.wldcfwc-sub-nav-button-group' ).find( '.wldcfwc-sub-nav' ).each(
						function (index, elm) {
							var content_id_each = $( this ).attr( 'data-content-id' );
							if (content_id == content_id_each) {
								$( '#' + content_id_each ).hide();
								$( '#' + content_id_each ).removeClass( 'wldcfwc-hide' );
								$( '#' + content_id_each ).fadeIn();
								$( this ).addClass( 'active' );
							} else {
								$( '#' + content_id_each ).addClass( 'wldcfwc-hide' );
								$( this ).removeClass( 'active' );
							}
						}
					);
					wldcfwc_submitButtonInViewport();
				}
			);

			// WLDC
			// button style show/hide
			$.fn.wldcfwc_show_button_style = function (call_mode) {
				var elms = $( '[id^="wishlist_button_style__"]' );
				elms.each(
					function () {
						var elm  = this;
						var mode = elm.id.replace( 'wishlist_button_style__', '' );
						var val  = $( elm ).find( ":selected" ).val();
						if (val == 'custom_button') {
							$( '.wldcfwc-show_hide_button_style__' + mode ).hide().removeClass( 'wldcfwc-hide' ).fadeIn();
							$( '.wldcfwc-show_hide_icon_button_padding_style__' + mode ).removeClass( 'wldcfwc-hide' );
						} else if (val == 'theme_button') {
							$( '.wldcfwc-show_hide_button_style__' + mode ).addClass( 'wldcfwc-hide' );
							$( '.wldcfwc-show_hide_icon_button_padding_style__' + mode ).removeClass( 'wldcfwc-hide' );
						}  else if (val == 'copy_style') {
							$( '#wldcfwc_section__copy_style_' + mode ).addClass( 'wldcfwc-hide' );
						} else if (val == 'custom_button_using_css') {
							$( '.wldcfwc-show_hide_button_style__' + mode ).addClass( 'wldcfwc-hide' );
						} else if (val == 'text_link') {
							$( '.wldcfwc-show_hide_button_style__' + mode ).addClass( 'wldcfwc-hide' );
							$( '.wldcfwc-show_hide_icon_button_padding_style__' + mode ).addClass( 'wldcfwc-hide' );
						} else {
							$( '.wldcfwc-show_hide_button_style__' + mode ).addClass( 'wldcfwc-hide' );
							$( '.wldcfwc-show_hide_icon_button_padding_style__' + mode ).addClass( 'wldcfwc-hide' );
						}
					}
				);
				wldcfwc_submitButtonInViewport();

			}
			$.fn.wldcfwc_show_button_style();
			$( '[id^="wishlist_button_style__"]' ).each(
				function () {
					$( this ).on(
						'change',
						function () {
							$.fn.wldcfwc_show_button_style('change');
						}
					);
				}
			);

			// button position show/hide
			$.fn.wldcfwc_wishlist_button_position = function (mode) {
				var elms = $( '[id^="wishlist_button_position__"]' );
				elms.each(
					function () {
						var elm  = this;
						var mode = elm.id.replace( 'wishlist_button_position__', '' );
						var val  = $( elm ).find( ':selected' ).val();
						if (val != 'wishlist_button_hide') {
							// always show unless it's hidden or an html button.
							// show postion, except for hidden button or custom html
							// this includes positioned button and modal buttons that's opened
							$( '.wldcfwc-show_hide_button_position__' + mode ).hide().removeClass( 'wldcfwc-hide' ).fadeIn();
							$( '#begin_wishlist_button_position__' + mode ).hide().removeClass( 'wldcfwc-hide' ).fadeIn();

							// show or hide custom button settings. this overrides above, based on button style settings
							$.fn.wldcfwc_show_button_style();
						} else {
							$( '.wldcfwc-show_hide_button_position__' + mode ).addClass( 'wldcfwc-hide' );
							$( '#begin_wishlist_button_position__' + mode ).hide().addClass( 'wldcfwc-hide' );
						}

						$( '.wldcfwc-show_hide_wishlist_button_use_shortcode__' + mode ).addClass( 'wldcfwc-hide' );
						$( '.wldcfwc-show_hide_wishlist_button_use_javascript__' + mode ).addClass( 'wldcfwc-hide' );
						if (val == 'wishlist_button_use_shortcode') {
							$( '.wldcfwc-show_hide_wishlist_button_use_shortcode__' + mode ).hide().removeClass( 'wldcfwc-hide' ).fadeIn();
						} else if (val == 'wishlist_button_use_javascript') {
							$( '.wldcfwc-show_hide_wishlist_button_use_javascript__' + mode ).hide().removeClass( 'wldcfwc-hide' ).fadeIn();
						}
					}
				);
				wldcfwc_submitButtonInViewport();
			}
			$.fn.wldcfwc_wishlist_button_position();
			$( '[id^="wishlist_button_position__"]' ).each(
				function () {
					$( this ).on(
						'change',
						function () {
							$.fn.wldcfwc_wishlist_button_position();
						}
					);
				}
			);
			$.fn.wldcfwc_show_button_spinner_options = function () {
				var radio        = $( 'input[type="radio"][name^="wishlist-dot-com-for-woocommerce[show_add2wishlist_button_spinner]"]:checked' );
				var val          = radio.val();
				var section_html = $( '.wldcfwc-show_hide_section__button_spinner' );
				if (section_html && val == 'no_str') {
					section_html.addClass( 'wldcfwc-hide' );
				} else if (section_html && val == 'yes_str') {
					if (section_html.hasClass( 'wldcfwc-hide' )) {
						section_html.hide().removeClass( 'wldcfwc-hide' ).fadeIn();
					}
				}
				wldcfwc_submitButtonInViewport();
			}
			$.fn.wldcfwc_show_button_spinner_options();
			$( 'input[type="radio"][name^="wishlist-dot-com-for-woocommerce[show_add2wishlist_button_spinner]"]' ).on(
				'click',
				function () {
					$.fn.wldcfwc_show_button_spinner_options();
				}
			);
			$.fn.wldcfwc_show_copy_style = function () {
				// loop over wldcfwc_show_copy_style radios
				var radios = $( 'input[type="radio"][name^="wishlist-dot-com-for-woocommerce[copy_style__"]' );
				radios.each(
					function () {
						var radio   = this;
						var mode    = radio.name.replace( 'wishlist-dot-com-for-woocommerce[copy_style__', '' );
						mode        = mode.replace( ']', '' );
						var val     = radio.value;
						var section = $( '#wldcfwc_section__copy_style_' + mode );
						if (section && radio.checked && val == 'yes_str') {
							section.addClass( 'wldcfwc-hide' );
						} else if (section && radio.checked && val == 'no_str') {
							section.hide().removeClass( 'wldcfwc-hide' ).fadeIn();
						}
					}
				);
				wldcfwc_submitButtonInViewport();
			}
			$.fn.wldcfwc_show_copy_style();
			$( 'input[type="radio"][name^="wishlist-dot-com-for-woocommerce[copy_style__"]' ).each(
				function () {
					$( this ).on(
						'change',
						function () {
							$.fn.wldcfwc_show_copy_style();
						}
					);
				}
			);

			$.fn.wldcfwc_show_header_footer_html = function () {
				// loop over wldcfwc_show_copy_style radios
				var radios = $( 'input[type="radio"][name^="wishlist-dot-com-for-woocommerce[wlcom_wishlist_template_use_html__"]' );
				radios.each(
					function () {
						var radio        = this;
						var mode         = radio.name.replace( 'wishlist-dot-com-for-woocommerce[wlcom_wishlist_template_use_html__', '' );
						mode             = mode.replace( ']', '' );
						var val          = radio.value;
						var section_html = $( '.wldcfwc-show_hide_section__wishlist_template_html__' + mode );
						if (section_html && radio.checked && val == 'no_str') {
							section_html.addClass( 'wldcfwc-hide' );
						} else if (section_html && radio.checked && val == 'yes_str') {
							if (section_html.hasClass( 'wldcfwc-hide' )) {
								section_html.hide().removeClass( 'wldcfwc-hide' ).fadeIn();
							}
						}
						var section_items = $( '.wldcfwc-show_hide_section__wishlist_template_items__' + mode );
						if (section_items && radio.checked && val == 'yes_str') {
							section_items.addClass( 'wldcfwc-hide' );
						} else if (section_items && radio.checked && val == 'no_str') {
							if (section_items.hasClass( 'wldcfwc-hide' )) {
								section_items.hide().removeClass( 'wldcfwc-hide' ).fadeIn();
							}
						}
					}
				);
				wldcfwc_submitButtonInViewport();
			}
			$.fn.wldcfwc_show_header_footer_html();
			$( 'input[type="radio"][name^="wishlist-dot-com-for-woocommerce[wlcom_wishlist_template_use_html__"]' ).each(
				function () {
					$( this ).on(
						'change',
						function () {
							$.fn.wldcfwc_show_header_footer_html();
						}
					);
				}
			);
			// menu table
			$( '#wldcfwc_header_menu' ).on(
				'click',
				'.wldcfwc-tr-up',
				function (event) {
					event.preventDefault();
					var row = $( this ).closest( 'tr' );
					if (row.index() > 0) { // Check if it's not the first row
						row.fadeOut(
							'fast',
							function () {
								row.prev().before( row.fadeIn( 'fast' ) );
							}
						);
					}
					row.find( '.wldcfwc-tr-up' ).hide();
					row.find( '.wldcfwc-tr-down' ).hide();
					setTimeout( $.fn.updateHeaderMenueRowButtons, 500 );
				}
			);
			$( '#wldcfwc_header_menu' ).on(
				'click',
				'.wldcfwc-tr-down',
				function (event) {
					event.preventDefault();
					var row     = $( this ).closest( 'tr' );
					var numRows = $( '#wldcfwc_header_menu tr' ).length;
					if (row.index() < numRows - 2) { // Check if it's not the last row
						row.fadeOut(
							'fast',
							function () {
								row.next().after( row.fadeIn( 'fast' ) );
							}
						);
					}
					row.find( '.wldcfwc-tr-up' ).hide();
					row.find( '.wldcfwc-tr-down' ).hide();
					setTimeout( $.fn.updateHeaderMenueRowButtons, 500 );
				}
			);
			$( '#wldcfwc_header_menu' ).on(
				'click',
				'.wldcfwc-tr-delete',
				function (event) {
					event.preventDefault();
					$( this ).closest( 'tr' ).fadeOut(
						'fast',
						function () {
							$( this ).remove();
						}
					);
					setTimeout( $.fn.updateHeaderMenueRowButtons, 500 );
				}
			);
			$( '.wldcfwc-tr-add' ).click(
				function (event) {
					event.preventDefault();
					var table         = $( this ).closest( 'table' );
					var firstRowClone = table.find( 'tr:first' ).clone();
					firstRowClone.find( 'input[type="text"]' ).val( '' );
					firstRowClone.hide().insertBefore( table.find( 'tr:last' ) ).fadeIn( 'fast' ); // Fade in new row
					setTimeout( $.fn.updateHeaderMenueRowButtons, 500 );
				}
			);
			$.fn.updateHeaderMenueRowButtons = function () {
				var $table = $( '#wldcfwc_header_menu' );
				var $rows  = $table.find( 'tr' );
				$rows.find( '.wldcfwc-tr-up, .wldcfwc-tr-down' ).show();
				$rows.first().find( '.wldcfwc-tr-up' ).hide();
				$rows.eq( -2 ).find( '.wldcfwc-tr-down' ).hide();
			}
			$.fn.updateHeaderMenueRowButtons();
			$.fn.wldcfwc_escapeHTML          = function (unsafe) {
				if (unsafe) {
					return unsafe.replace(
						/[&<>"']/g,
						function (m) {
							switch (m) {
								case '&':
									return '&amp;';
								case '<':
									return '&lt;';
								case '>':
									return '&gt;';
								case '"':
									return '&quot;';
								default:
									return '&#039;';
							}
						}
					);
				}
			};

			// button icons
			$.fn.wldcfwc_formatSVGSelect = function (icon) {
				var type   = $( icon.element ).data( 'type' );
				var source = $( icon.element ).data( 'source' );
				if ( ! icon.id) {
					return icon.text;
				}
				if (type == 'svg') {
					return $( '<div class="wldcfwc_icon_drop_svg">' + $( icon.element ).data( 'source' ) + '</div><div class="wldcfwc_icon_drop_text">' + icon.text + '</div>' );
				} else if (type == 'image' || type == 'wldcfwc_icon') {
					return $( '<div class="wldcfwc_icon_drop_image"><img src="' + $( icon.element ).data( 'source' ) + '"></img></div><div class="wldcfwc_icon_drop_text">' + icon.text + '</div>' );
				} else {
					return $( '<div class="wldcfwc_icon_drop_svg"></div><div class="wldcfwc_icon_drop_text">' + icon.text + '</div>' );
				}
			}
			if (typeof $.fn.select2 != 'undefined') {
				// to fix [object Object], see bug: https://github.com/woocommerce/selectWoo/issues/39

				setTimeout(
					function () {
						$.fn.select2.amd.define(
							'customSingleSelectionAdapter',
							[
							'select2/utils',
							'select2/selection/single',
							],
							function (Utils, SingleSelection) {
								const adapter            = SingleSelection;
								adapter.prototype.update = function (data) {
									if (data.length === 0) {
										this.clear();
										return;
									}
									var selection = data[0];
									var $rendered = this.$selection.find( '.select2-selection__rendered' );
									var formatted = this.display( selection, $rendered );
									$rendered.empty().append( formatted );
									$rendered.prop( 'title', selection.title || selection.text );
								};
								return adapter;
							}
						);

						$( "#addwishlist_button_icon__product_page, #addwishlist_button_icon__product_loop, #addwishlist_button_icon__cart, #addwishlist_button_icon__hp_mywishlist, #addwishlist_button_icon__hp_findwishlist, #addwishlist_button_icon__hp_createwishlist" ).select2(
							{
								multiple: false,
								tags: false,
								theme: "bootstrap-5",
								minimumResultsForSearch: Infinity,
								width:'380px;',
								templateSelection: $.fn.wldcfwc_formatSVGSelect,
								templateResult: $.fn.wldcfwc_formatSVGSelect,
								selectionAdapter: $.fn.select2.amd.require( 'customSingleSelectionAdapter' )
							}
						);
					},
					50
				)
			}
			$.fn.wldcfwc_select_button_icon = function (mode) {
				// show/hide sections based on selected item

				var sel_obj = $( '#addwishlist_button_icon__' + mode ).find( ":selected" );
				var type    = sel_obj.data( 'type' );
				var source  = sel_obj.data( 'source' );

				// show/hide custom
				if (type == 'custom_image') {
					$( '.wldcfwc-addwishlist_button_icon_image_upload__' + mode ).removeClass( 'wldcfwc-hide' );
					// hide icon color
					$( '.wldcfwc-show_hide_icon_color__' + mode ).addClass( 'wldcfwc-hide' );
					// show styles that aren't color
					$( '.wldcfwc-show_hide_icon_style__' + mode ).not( '.wldcfwc-show_hide_icon_color__' + mode ).css( 'opacity', 0 ).removeClass( 'wldcfwc-hide' ).animate( { opacity: 1 }, 500 );
				} else {
					$( '.wldcfwc-addwishlist_button_icon_image_upload__' + mode ).addClass( 'wldcfwc-hide' );
				}
				if (type == 'wldcfwc_icon') {
					// hide icon color
					$( '.wldcfwc-show_hide_icon_color__' + mode ).addClass( 'wldcfwc-hide' );
					// show styles that aren't color
					$( '.wldcfwc-show_hide_icon_style__' + mode ).not( '.wldcfwc-show_hide_icon_color__' + mode ).css( 'opacity', 0 ).removeClass( 'wldcfwc-hide' ).animate( { opacity: 1 }, 500 );
				} else if (type !== 'none') {
					// show icon style
					if (type == 'svg') {
						// show with color
						$( '.wldcfwc-show_hide_icon_style__' + mode ).css( 'opacity', 0 ).removeClass( 'wldcfwc-hide' ).animate( { opacity: 1 }, 500 );
					} else {
						// show styles that aren't color
						$( '.wldcfwc-show_hide_icon_style__' + mode ).not( '.wldcfwc-show_hide_icon_color__' + mode ).css( 'opacity', 0 ).removeClass( 'wldcfwc-hide' ).animate( { opacity: 1 }, 500 );
					}
				} else {
					$( '.wldcfwc-show_hide_icon_style__' + mode ).addClass( 'wldcfwc-hide' );
				}
			}
			// page icon
			var wldcfwc_items = ['product_page','product_loop','cart','hp_mywishlist','hp_findwishlist','hp_createwishlist'];
			for (var i = 0; i < wldcfwc_items.length; i++) {
				(function (index) {
					$( '#addwishlist_button_icon__' + wldcfwc_items[index] ).on(
						'change',
						function (event) {
							$.fn.wldcfwc_select_button_icon( wldcfwc_items[index] );
						}
					);
					$.fn.wldcfwc_select_button_icon( wldcfwc_items[index] );
				})( i );
			}

			$( '#advanced_add2wishlist_button_template_link' ).on(
				'click',
				function (event) {
					event.preventDefault();
					$( '#advanced_add2wishlist_button_template_content' ).hide().toggleClass( 'wldcfwc-hide' ).fadeIn();
				}
			);

			$( '.wldcfwc-button-md-js' ).on(
				'click',
				function () {
					var tab_name = $( this ).attr( 'data-toptab' );
					var tab      = $( 'li[data-section=' + tab_name + ']' );
					$( tab ).click();
					//$( 'html, body' ).animate( {scrollTop : 0}, 600 );
				}
			);

			wldcfwc_submitButtonInViewport();

			$( '.wldcfwc-expand-button' ).on(
				'click',
				function (elm) {
					var parent_div_class = $( this ).attr( 'data-expand-parent-div-class' );
					if (parent_div_class) {
						var parent_div = $( this ).closest( '.' + parent_div_class );
						if (parent_div) {
							var target_div = parent_div.find( '.wldcfwc-expand-target' );
						}
						var arrow_icon = $( this ).find( '.wldcfwc-arrow-icon' );
						if (target_div && target_div.hasClass( 'wldcfwc-hide' )) {
							// showing
							if (arrow_icon) {
								arrow_icon.addClass( 'wldcfwc-arrow-icon-up' );
							}
							target_div.hide();
							target_div.removeClass( 'wldcfwc-hide' );
							target_div.show( 'fade' );
						} else {
							if (arrow_icon) {
								arrow_icon.removeClass( 'wldcfwc-arrow-icon-up' );
							}
							target_div.hide( 'fade' );
							target_div.addClass( 'wldcfwc-hide' );
						}
					}
				}
			);

			if (typeof $.fn.select2 != 'undefined') {
				$( '[data-select2-sku]' ).select2(
					{
						placeholder: 'Type 3 characters to search',
						theme: 'bootstrap-5',
						minimumInputLength: 3,
						width: 'style',
						language: {
							errorLoading: function () {
								return "Couldn't load results. Try refreshing the page";
							}
						},
						ajax: {
							url: ajaxurl,
							dataType: 'json',
							delay: 250,
							data: function (params) {
								return {
									term: params.term, // search term
									action: 'wldcfwc_get_product_droppown_rec_ajax', // WordPress AJAX action
									_wpnonce: wldcfwc_object_admin.nonce.wldcfwc_get_product_droppown_rec_ajax,
									mode: 'sku'
								};
							},
							processResults: function (data) {
								return {
									results: data
								};
							}
						}
					}
				);
				$( '[data-select2-category]' ).select2(
					{
						placeholder: 'Type 3 characters to search',
						theme: 'bootstrap-5',
						minimumInputLength: 3,
						width: 'style',
						language: {
							errorLoading: function () {
								return "Couldn't load results. Try refreshing the page";
							}
						},
						ajax: {
							url: ajaxurl,
							dataType: 'json',
							delay: 250,
							data: function (params) {
								return {
									term: params.term, // search term
									action: 'wldcfwc_get_product_droppown_rec_ajax', // WordPress AJAX action
									_wpnonce: wldcfwc_object_admin.nonce.wldcfwc_get_product_droppown_rec_ajax,
									mode: 'category'
								};
							},
							processResults: function (data) {
								return {
									results: data
								};
							}
						}
					}
				);
			}
		}
	);

	// Receive message from js used to for getting theme colors
	const channel = new BroadcastChannel('wldcfwc-wishlist-dot-com-tabs');
	channel.onmessage=function(e){
		if(e.data.hasOwnProperty('wldcfwc_message')){
			var get_theme_primary_colors = (e.data.wldcfwc_message && e.data.wldcfwc_message?.get_theme_primary_colors) || {};
			if(get_theme_primary_colors){
				getThemePrimaryColorsAndPopulate();
			}
		}
	}
	function saveForm(mode){
		var save_settings_button = document.getElementById( 'wishlist-dot-com-for-woocommerce-save-id' );
		if (save_settings_button) {
			if(mode && mode == 'quiet'){
				save_settings_button.setAttribute('quiet_save',1);
			}
			save_settings_button.click();
		}
	}

}
document.addEventListener(
	"DOMContentLoaded",
	function (e) {
		wldcfwc_init_admin();
	}
);
