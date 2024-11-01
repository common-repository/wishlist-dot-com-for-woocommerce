function wldcfwc_get_theme_styles_init() {
	//for comminication between windows
	const channel = new BroadcastChannel('wldcfwc-wishlist-dot-com-tabs');
	// Helper function to get URL parameters
	function getUrlParameter(param) {
		param = param.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
		var regex = new RegExp('[\\?&]' + param + '=([^&#]*)');
		var results = regex.exec(location.search);
		return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
	}

	// Function to find the largest icon in the header
	function findLargestIconInHeader() {
		let largestIcon = { href: '', size: 0 };
		const links = document.head.getElementsByTagName('link');

		Array.from(links).forEach(link => {
			if (link.rel.includes('icon') && link.sizes.value) {
				const sizes = link.sizes.value.split('x');
				if (sizes.length === 2) {
					const size = parseInt(sizes[0]);
					if (size > largestIcon.size) {
						largestIcon = { href: link.href, size: size };
					}
				} else if (link.sizes.value.toLowerCase() === 'any' && largestIcon.size === 0) {
					largestIcon = { href: link.href, size: -1 }; // 'any' size
				}
			}
		});

		return largestIcon;
	}

	// Function to capture button and font styles
	function getStyles(element, type) {
		var styles = {};
		var elementStyles = window.getComputedStyle(element);

		if (type === 'button') {
			styles = {
				color: elementStyles.color,
				backgroundColor: elementStyles.backgroundColor,
				borderColor: elementStyles.borderColor
			};
		} else if (type === 'font') {
			styles = {
				color: elementStyles.color,
				fontSize: elementStyles.fontSize,
				lineHeight: elementStyles.lineHeight,
				fontWeight: elementStyles.fontWeight,
				fontFamily: elementStyles.fontFamily
			};
		} else if (type === 'link') {
			const textDecorationLine = elementStyles.textDecorationLine || elementStyles.textDecoration.split(' ')[0];
			styles = { textDecoration: textDecorationLine };
		}

		return styles;
	}

	// Function to get store name styles
	function getStoreNameStyle() {
		var largestFontSize = 0, largestLineHeight = 0, storeNameStyle = {};

		var wlcom_your_store_name = wldcfwc_store_name;
		var store_url = wldcfwc_store_url;

		document.querySelectorAll('a').forEach(element => {
			var textContent = element.textContent.trim();
			if (textContent.toLowerCase() === wlcom_your_store_name.toLowerCase() && (store_url.includes(element.href) || element.href.includes(store_url))) {
				var style = getStyles(element, 'font');
				var fontSize = parseFloat(style.fontSize);
				var lineHeight = parseFloat(style.lineHeight);
				if (fontSize > largestFontSize || lineHeight > largestLineHeight) {
					storeNameStyle = style;
					largestFontSize = fontSize;
					largestLineHeight = lineHeight;
				}
			}
		});
		return storeNameStyle;
	}

	// Function to get menu item styles
	function getMenuItemStyle() {
		var largestFontSize = 0, largestLineHeight = 0, menuItemStyle = {};

		// Check which URL is not part of the current location
		var currentLocation = window.location.href;

		// Select the title and URL of the non-active menu item
		var nonActiveTitle, nonActiveUrl;

		//find the non active menu item
		if (wldcfwc_shop_url && !currentLocation.includes(wldcfwc_shop_url)) {
			nonActiveTitle = wldcfwc_shop_page_title;
			nonActiveUrl = wldcfwc_shop_url;
		} else if (wldcfwc_wl_hp_url && !currentLocation.includes(wldcfwc_wl_hp_url)) {
			nonActiveTitle = wldcfwc_wl_hp_page_title;
			nonActiveUrl = wldcfwc_wl_hp_url;
		} else if (wldcfwc_cart_url && !currentLocation.includes(wldcfwc_cart_url)) {
			nonActiveTitle = wldcfwc_cart_page_title;
			nonActiveUrl = wldcfwc_cart_url;
		} else {
			return menuItemStyle;
		}

		// Find the non-active menu item in the DOM and extract its style
		document.querySelectorAll('a').forEach(element => {
			var textContent = element.textContent.trim();
			if (textContent && textContent.toLowerCase() === nonActiveTitle.toLowerCase() && nonActiveUrl === element.href) {
				var fontStyle = getStyles(element, 'font');
				var linkStyle = getStyles(element, 'link');
				var combinedStyle = { ...fontStyle, ...linkStyle };
				var fontSize = parseFloat(combinedStyle.fontSize);
				var lineHeight = parseFloat(combinedStyle.lineHeight);
				if (fontSize > largestFontSize || lineHeight > largestLineHeight) {
					menuItemStyle = combinedStyle;
					largestFontSize = fontSize;
					largestLineHeight = lineHeight;
				}
			}
		});

		return menuItemStyle;
	}


	// Function to get default body font styles
	function getDefaultFontStyles() {
		var pelm = document.createElement('p');
		pelm.id = 'pelm_id';
		pelm.textContent = 'my default font';
		pelm.className = 'wldcfwc-hide';
		document.body.appendChild(pelm);
		var bodyFontStyle = getStyles(document.getElementById('pelm_id'), 'font');
		document.body.removeChild(pelm);
		return bodyFontStyle;
	}

	// Create buttons to capture their styles
	function createButton(id, text, className) {
		var btn = document.createElement('button');
		btn.id = id;
		btn.textContent = text;
		btn.className = className;
		document.body.appendChild(btn); // Append to body
		return btn;
	}

	// Get WooCommerce page styles
	var theme_primary_colors_mapped = {};
	function getThemeStyling() {

		// Get logo icon
		var largest_icon = findLargestIconInHeader();
		var icon_src = largest_icon?.href || '';

		// Get store name styles
		var storeNameStyle = getStoreNameStyle();

		// Get menu item styles
		var menuItemStyle = getMenuItemStyle();

		// Get body font styles
		var bodyFontStyle = getDefaultFontStyles();

		// Create primary and danger buttons and capture their styles
		var primaryButton = createButton('primaryButton', 'Primary', 'btn btn-primary wldcfwc-hide');
		var dangerButton = createButton('dangerButton', 'Danger', 'btn btn-danger wldcfwc-hide');
		var primaryButtonStylesRegular = getStyles(primaryButton, 'button');
		var dangerButtonStylesRegular = getStyles(dangerButton, 'button');

		// Capture hover styles for buttons
		setTimeout(function () {
			primaryButton.focus();
			var primaryButtonStylesHover = getStyles(primaryButton, 'button');
			dangerButton.focus();
			var dangerButtonStylesHover = getStyles(dangerButton, 'button');

			// Consolidate all captured styles
			var theme_primary_colors = {
				'primaryButtonStylesRegular': primaryButtonStylesRegular,
				'primaryButtonStylesHover': primaryButtonStylesHover,
				'dangerButtonStylesRegular': dangerButtonStylesRegular,
				'dangerButtonStylesHover': dangerButtonStylesHover,
				'iconSrc': icon_src,
				'storeNameStyle': storeNameStyle,
				'menuItemStyle': menuItemStyle,
				'bodyFontStyle': bodyFontStyle
			};

			// Map the captured styles to form field names
			// Button suffixes and style variables for mapping
			// Note the order of each array
			var button_suffix = ['primary_button', 'share_button', 'danger_button'];
			var style_vars = ['backgroundColor', 'color', 'borderColor'];
			var field_names = ['background_color', 'text_color', 'border_color'];

			var primary_background_color, danger_background_color, button_cat, set_danger_button = false;
			// Map primary and danger button styles to form field names
			for (var i = 0; i < button_suffix.length; i++) {
				for (var k = 0; k < style_vars.length; k++) {
					let color = '', color_hover = '';
					if (button_suffix[i] === 'primary_button' || button_suffix[i] === 'share_button') {
						// Map primary button styles
						color = theme_primary_colors.primaryButtonStylesRegular[style_vars[k]] || '';
						color_hover = theme_primary_colors.primaryButtonStylesHover[style_vars[k]] || '';
						if(style_vars[k] == 'backgroundColor'){
							primary_background_color = color;
						}
						button_cat = 'primary';
					} else {
						// Map danger button styles
						color = theme_primary_colors.dangerButtonStylesRegular[style_vars[k]] || '';
						color_hover = theme_primary_colors.dangerButtonStylesHover[style_vars[k]] || '';
						if(style_vars[k] == 'backgroundColor'){
							danger_background_color = color;
						}
						button_cat = 'danger';
					}
					//don't set danger if its color is the same as primary
					if(style_vars[k] == 'backgroundColor' && button_cat === 'danger' && primary_background_color.length > 0 && danger_background_color.length > 0 && danger_background_color !== primary_background_color){
						set_danger_button = true;
					}
					if (color.length > 0 && (button_cat !== 'danger' || set_danger_button)) {
						theme_primary_colors_mapped['wlcom_wishlist_button_' + field_names[k] + '__' + button_suffix[i]] = color;
					}
					if (color_hover.length > 0 && (button_cat !== 'danger' || set_danger_button)) {
						theme_primary_colors_mapped['wlcom_wishlist_button_' + field_names[k] + '_hover__' + button_suffix[i]] = color_hover;
					}
				}
			}

			// Map the icon source
			if (theme_primary_colors.iconSrc) {
				theme_primary_colors_mapped['wlcom_plgn_your_store_icon'] = theme_primary_colors.iconSrc;
			}

			// Map the store name style
			if (theme_primary_colors.storeNameStyle?.color?.length > 0) {
				theme_primary_colors_mapped['wlcom_template_store_name__text_color'] = theme_primary_colors.storeNameStyle.color;
			}

			// Map menu item styles
			if (theme_primary_colors.menuItemStyle?.color?.length > 0) {
				theme_primary_colors_mapped['wlcom_header_template_menu_item__text_color'] = theme_primary_colors.menuItemStyle.color;
			}
			if (theme_primary_colors.menuItemStyle?.fontSize?.length > 0) {
				theme_primary_colors_mapped['wlcom_header_template_menu_item__text_font_size'] = theme_primary_colors.menuItemStyle.fontSize;
			}
			if (theme_primary_colors.menuItemStyle?.textDecoration?.length > 0) {
				theme_primary_colors_mapped['wlcom_header_template_menu_item__text_decoration'] = theme_primary_colors.menuItemStyle.textDecoration;
			}

			// Map body font styles
			if (theme_primary_colors.bodyFontStyle?.color?.length > 0) {
				theme_primary_colors_mapped['wlcom_text_color'] = theme_primary_colors.bodyFontStyle.color;
			}

			// Send the mapped object to the server using AJAX
			var wp_ajax_obj = {
				api_action: 'save_theme_styles',
				theme_primary_colors: theme_primary_colors_mapped
			};

			doWooAPIAjaxCall(wp_ajax_obj)
				.then(function(response) {
					// This will be called after the AJAX request is successfully completed
					updateAdminPanelMessage(theme_primary_colors_mapped);
				})
				.catch(function(jqXHR, textStatus, errorThrown) {
					// Handle the case where the AJAX call fails
					console.error('AJAX call failed:', textStatus, errorThrown);
				});


		}, 100);
	}

	// Trigger the process to get theme styles
	setTimeout(getThemeStyling, 200);

	// AJAX function to send data to the server
	function doWooAPIAjaxCall(data) {
		return fetch(wldcfwc_api_settings.root + 'wldcfwc_wishlistdotcom/v1/wldcfwc_api/', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': wldcfwc_api_settings.nonce
			},
			body: JSON.stringify(data)
		})
		.then(response => {
			if (!response.ok) {
				throw new Error('Network response was not ok ' + response.statusText);
			}
			return response.json();
		})
		.catch(error => console.error('There was a problem with the fetch operation:', error));
	}

	function updateAdminPanelMessage(theme_primary_colors){
		var message = {
			wldcfwc_message: {
				//'theme_primary_colors':theme_primary_colors
				'get_theme_primary_colors':'yes'
			}
		};
		channel.postMessage(message);
	}
}

// Initialize the script after DOM is fully loaded
document.addEventListener("DOMContentLoaded", function () {
	wldcfwc_get_theme_styles_init();
});
