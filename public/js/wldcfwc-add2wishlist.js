
function wldcfwc_init()
{
    var input_type = 'woocomm_wl';
    var this_protocol = 'https:';

    if(typeof wldcfwc_api_domain_mode == 'undefined') {
        wldcfwc_api_domain_mode = 'ap[mode]w.wishlist.com';
    }

    var api_domain_mode = wldcfwc_api_domain_mode;
    var base_url_mode = this_protocol+'//'+api_domain_mode;

    var base_url_ui = base_url_mode.replace('[mode]','u');

    var wooCommApi_url_mode = base_url_mode+'/wooCommApi';

    var wooCommApi_url_ui = wooCommApi_url_mode.replace('[mode]','u');
    var add2wishlistUrl_ui = wooCommApi_url_mode.replace('[mode]','u');
    var add2wishlistUrl_popwin_ui = wooCommApi_url_mode.replace('[mode]','p');

    var return_url = document.location.href.replace(/#/g,'');

    var wldcfwc_myWishlists = base_url_ui+'/mywishlists';
    var wldcfwc_hp = base_url_ui;
    var wooCommApi_mywishlist_ui = wooCommApi_url_ui+'?api_action=mywishlist';

    var blank_url = base_url_ui+'/blank';

    var wishData = {};
    var formHTML = "<form class=\"wldcfwc_form\" id=\"wldcfwc_form\" name=\"wldcfwc_form\" target=\"_blank\" method=\"post\"><input id=\"wldcfwc_input_type\" type=\"hidden\" name=\"input_type\" value=\"\"/><input id=\"wldcfwc_api_action\" type=\"hidden\" name=\"api_action\" value=\"add2wishlist\"/><input id=\"wldcfwc_is_popwin\" type=\"hidden\" name=\"is_popwin\" value=\"1\"><input id=\"wldcfwc_auto_save_wish\" type=\"hidden\"  name=\"auto_save_wish\" value=\"\"><input id=\"wldcfwc_is_hidden_post\" type=\"hidden\"  name=\"is_hidden_post\" value=\"\"><input id=\"wldcfwc_return_url\" type=\"hidden\"  name=\"return_url\" value=\"\"><input id=\"wldcfwc_product_id\" type=\"hidden\" name=\"product_id\" value=\"\"><input id=\"wldcfwc_wish_data\" type=\"hidden\" name=\"wish_data\" value=\"\"></form>";

    var wldcfwc_current_frame_url = '';

    if(typeof wldcfwc_store_uuid == 'undefined') {
        wldcfwc_store_uuid = 'na';
    }

    function generateUniqueID()
    {
        const timestamp = Date.now(); // Get current time in milliseconds.
        const randomComponent = Math.random().toString(36).substring(2, 15); // Generate a random string.
        // Combine all components into one string
        return `${timestamp}${randomComponent}`;
    }
    function setCookie(cname, cvalue, exdays)
    {
        const d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        let expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }
    function getCookie(cname)
    {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for(let i = 0; i <ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
    function deleteCookie(cname)
    {
        if(getCookie(cname) ) {
            let expires = "expires=Thu, 01 Jan 1970 00:00:01 GMT";
            document.cookie = cname + "=;" + expires + ";path=/";
        }
    }
    function thisFadeIn(obj,remove_css_class)
    {
        if(obj.classList.contains(remove_css_class)) {
            var opacity_var = 0;
            obj.style.opacity = '0%';
            obj.classList.remove(remove_css_class);
            var max_cnt = 50,cnt = 0;
            var fadeEffect = setInterval(
                function () {
                    if (opacity_var < 100 && cnt < max_cnt) {
                        opacity_var = opacity_var + 10;
                        obj.style.opacity = opacity_var+'%';
                    } else {
                        obj.style.opacity = '100%';
                        clearInterval(fadeEffect);
                    }
                    cnt++;
                }, 20
            );
        }
    }
    function shippingAddressAlert()
    {
        var woocommerce_notices_wrapper = document.querySelector('#wldcfwc_different_shipping_addresses_warning_id');
        if(woocommerce_notices_wrapper) {
            woocommerce_notices_wrapper.parentNode.classList.remove('wldcfwc-hide');
        }
    }
    function checkCartCheckoutGiftItems(reserved_products, screen)
    {
        var prod_id_delivery_addr_data = reserved_products;
        var wish_data, product_id, data={},prod_res_a=[],prev_recipient='',cur_address='',shipping_addresses_a=[],unique_street_addresses_a=[],prod_id_shipping_addresses_a=[],has_reserved_wishes=false;
        var has_missing_res_addresses=false,multiple_addresses=false,cur_recipient_snippet,cur_recipient_check;
        var data_elm_collection = document.getElementsByClassName('wldcfwc-data-span');
        for(var i=0; i<data_elm_collection.length; i++){
            var data_elm = data_elm_collection[i];
            var this_data = data_elm.getAttribute('data-wldcfwc-wish');
            try{
                wish_data = JSON.parse(this_data);
                if(wish_data.hasOwnProperty('wlprodid')) {
                    product_id = wish_data.wlprodid;
                    if(!prod_id_delivery_addr_data.hasOwnProperty(product_id)) {
                        has_missing_res_addresses = true;
                    }else{
                        prod_res_a = prod_id_delivery_addr_data[product_id];

                        if(prod_res_a.hasOwnProperty('delivery_address1')) {
                               cur_recipient_check = prod_res_a.delivery_first_name.slice(0,10)+prod_res_a.delivery_address1.slice(0,10);
                               cur_recipient_snippet = prod_res_a.delivery_first_name+' '+prod_res_a.delivery_last_name+', '+prod_res_a.delivery_address1;
                               cur_recipient_snippet = cur_recipient_snippet.slice(0,40);

                               prod_id_shipping_addresses_a[product_id] = cur_recipient_snippet;
                               var is_unique_address = false;
                            if(!unique_street_addresses_a.includes(cur_recipient_check)) {
                                unique_street_addresses_a.push(cur_recipient_check);
                                is_unique_address = true;
                            }
                            //update cart
                            if(screen == 'cart') {
                                   var product_name_data_span = document.getElementById('wldcfwc_data_span_'+product_id);
                                if(product_name_data_span) {
                                    var gift_text = "Gift for "+cur_recipient_snippet+'...';
                                    const div_gift = createElement(document,"div");
                                    div_gift.innerHTML = gift_text;
                                    product_name_data_span.parentNode.insertBefore(div_gift, product_name_data_span.nextSibling);
                                }
                            }
                        }
                        if(is_unique_address) {
                               shipping_addresses_a.push(prod_res_a);
                        }
                        has_reserved_wishes = true;
                        if(prev_recipient != '' && (prev_recipient != cur_recipient_check)) {
                            multiple_addresses = true;
                        }
                        if(prod_res_a.hasOwnProperty('delivery_address1')) {
                            prev_recipient = cur_recipient_check;
                        }
                    }
                }
            }catch (e)
            {
                //
            }
        };
        if(shipping_addresses_a.length > 0) {
            //insert shipping address message
            insertGiftAddresses(shipping_addresses_a);
            if((has_reserved_wishes && has_missing_res_addresses) || multiple_addresses) {
                //display warning
                shippingAddressAlert();
            }
        }
    }

    function copyAddressToShipping(curr_address_obj,screen)
    {

        //cart shipping address change link
        var change_address = document.querySelector('.shipping-calculator-button');
        if(change_address) {
            change_address.click();
        }

        //checkout shipping address checkbox
        var checkox = document.getElementById('ship-to-different-address-checkbox');
        if(checkox && !checkox.checked) {
            checkox.click();
        }
        if(curr_address_obj.hasOwnProperty('delivery_first_name')) {
            if(screen == 'checkout') {
                var field_prefix = ''
            }else{
                var field_prefix = 'calc_'
            }
            var field = document.getElementById(field_prefix+'shipping_first_name');
            if(curr_address_obj.delivery_first_name && field) {
                field.value = curr_address_obj.delivery_first_name;
            }
            var field = document.getElementById(field_prefix+'shipping_last_name');
            if(curr_address_obj.delivery_last_name && field) {
                field.value = curr_address_obj.delivery_last_name;
            }
            var field = document.getElementById(field_prefix+'shipping_address_1');
            if(curr_address_obj.delivery_address1 && field) {
                field.value = curr_address_obj.delivery_address1;
            }
            var field = document.getElementById(field_prefix+'shipping_address_2');
            if(curr_address_obj.delivery_address2 && field) {
                field.value = curr_address_obj.delivery_address2;
            }
            var field = document.getElementById(field_prefix+'shipping_city');

            if(curr_address_obj.delivery_city && field) {
                field.value = curr_address_obj.delivery_city;
            }
            //country before state
            var field = document.getElementById(field_prefix+'shipping_country');
            if(curr_address_obj.delivery_country && field) {
                const optionExists = Array.from(field.options).some(option => option.value === curr_address_obj.delivery_country);
                if(optionExists) {
                    field.value = curr_address_obj.delivery_country;
                    const event = new Event('change', { bubbles: true }); // Create a new change event
                    field.dispatchEvent(event); // Dispatch the event
                }
            }
            var field = document.getElementById(field_prefix+'shipping_state');
            if(curr_address_obj.delivery_state && field) {
                const optionExists = Array.from(field.options).some(option => option.value === curr_address_obj.delivery_state);
                if(optionExists) {
                    field.value = curr_address_obj.delivery_state;
                    const event = new Event('change', { bubbles: true }); // Create a new change event
                    field.dispatchEvent(event); // Dispatch the event
                }
            }
            var field = document.getElementById(field_prefix+'shipping_postcode');
            if(curr_address_obj.delivery_zipcode && field) {
                field.value = curr_address_obj.delivery_zipcode;
            }
        }
    }
    function insertGiftAddresses(shipping_addresses_a)
    {
        //cart or checkout
        var screen = 'checkout';
        var wldcfwc_shipping_address_msg_ul = document.querySelector('#wldcfwc_checkout_shipping_addresses_id');
        if(!wldcfwc_shipping_address_msg_ul) {
            screen = 'cart';
            wldcfwc_shipping_address_msg_ul = document.querySelector('#wldcfwc_cart_shipping_addresses_id');
        }

        if(wldcfwc_shipping_address_msg_ul) {
            //add shipping addresses
            var shipping_addresses_html = '';
            var checkout_shipping_addresses_a = shipping_addresses_a;
            if(checkout_shipping_addresses_a.length > 0) {
                var curr_address_obj;
                //note the use of let vs var so current i is passed to copyAddressToShipping(i)
                for (let i=0;i<checkout_shipping_addresses_a.length;i++){
                    //reset
                    shipping_addresses_html = '';

                    var notice_li = createElement(document,'li');
                    notice_li.setAttribute('id','wldcfwc_woocommerce_'+i);
                    notice_li.addEventListener(
                        'click',function () {
                            copyAddressToShipping(checkout_shipping_addresses_a[i],screen);
                        }
                    );

                       curr_address_obj = checkout_shipping_addresses_a[i];

                       //&& screen == 'checkout'
                    if(curr_address_obj.delivery_first_name) {
                        shipping_addresses_html = shipping_addresses_html + curr_address_obj.delivery_first_name+' '
                    }
                       //&& screen == 'checkout'
                    if(curr_address_obj.delivery_last_name) {
                        shipping_addresses_html = shipping_addresses_html + curr_address_obj.delivery_last_name+', '
                    }
                    if(curr_address_obj.delivery_address1) {
                        shipping_addresses_html = shipping_addresses_html + curr_address_obj.delivery_address1+', '
                    }
                    if(curr_address_obj.delivery_address2) {
                              shipping_addresses_html = shipping_addresses_html + curr_address_obj.delivery_address2+', '
                    }
                    if(curr_address_obj.delivery_city) {
                        shipping_addresses_html = shipping_addresses_html + curr_address_obj.delivery_city+', '
                    }
                    if(curr_address_obj.delivery_state) {
                        shipping_addresses_html = shipping_addresses_html + curr_address_obj.delivery_state+', '
                    }
                    if(curr_address_obj.delivery_zipcode) {
                        shipping_addresses_html = shipping_addresses_html + curr_address_obj.delivery_zipcode+', '
                    }
                    if(curr_address_obj.delivery_country) {
                        shipping_addresses_html = shipping_addresses_html + curr_address_obj.delivery_country+', '
                    }
                    //remove trailing ', '
                    shipping_addresses_html = shipping_addresses_html.replace(/, $/, '');
                    if(screen == 'checkout') {
                        var section_title = '<a href="javascript:void(0)">Copy gift address below</a>: ';
                    }else{
                        var section_title = '<a href="javascript:void(0)">Copy gift address to Cart totals</a>: ';
                    }
                    shipping_addresses_html = section_title+shipping_addresses_html;

                    notice_li.innerHTML = shipping_addresses_html;
                    wldcfwc_shipping_address_msg_ul.appendChild(notice_li);
                }

                if(shipping_addresses_html.length > 0) {
                    wldcfwc_shipping_address_msg_ul.classList.remove('wldcfwc-hide');
                    var checkox = document.getElementById('ship-to-different-address-checkbox');
                    if(checkox && !checkox.checked) {
                        checkox.click();
                    }
                }
            }
        }
    }
    function getUserReservedProductsSetShippingAddress()
    {
        var screen = 'checkout';
        var wldcfwc_shipping_address_msg_ul = document.querySelector('#wldcfwc_checkout_shipping_addresses_id');
        if(!wldcfwc_shipping_address_msg_ul) {
            screen = 'cart';
            wldcfwc_shipping_address_msg_ul = document.querySelector('#wldcfwc_cart_shipping_addresses_id');
        }
        if(wldcfwc_shipping_address_msg_ul) {
            //get user session
            var wp_ajax_obj= {api_action: 'wldcfwc_get_res_prods'};
            doWooAPIAjaxCall(wp_ajax_obj).then(
                response => {
                    if(response && response.wldcfwc_res_prods) {
                        //use user's reserved products to check items being puchased
                        checkCartCheckoutGiftItems(response.wldcfwc_res_prods, screen);
                    }
                }
            ).catch(
                error => {
                console.error('Error:', error); // Handle any errors
                }
            );

        }
    }
    getUserReservedProductsSetShippingAddress();
    function addOutsideModalClick(modal)
    {
        jQuery(document).on(
            'click.wldcfwc_modalClose', function (event) {
                if (!modal.is(event.target) && modal.has(event.target).length === 0) {
                    modal.remove();
                    removeOutsideModalClick();
                }
            }
        );
    }
    function removeOutsideModalClick()
    {
        jQuery(document).off('click.wldcfwc_modalClose');
    }
    function showModalWithCancelAndContinue(button_obj,continueCallback)
    {
        // Create the modal elements

        function close_modal()
        {
            jQuery('.wldcfwc-tooltip-modal-selector').remove();
            removeOutsideModalClick();
        }
        close_modal();

        var text = "Please select options before adding."
        //use jquery for css below
        var modal = jQuery("<div id='wldcfwc_tooltip_modal_id' class='wldcfwc-tooltip wldcfwc-tooltip-modal wldcfwc-tooltip-modal-selector'>"+text+"<div class='wldcfwc-tooltip-modal-links-div'><a id='wldcfwc-tooltip-modal-close-link' class='wldcfwc-tooltip-modal-link' href='javascript:void(0)'>Close</a><a id='wldcfwc-tooltip-modal-continue-link' class='wldcfwc-tooltip-modal-link' href='javascript:void(0)'>Skip</a></div></div></div>");
        jQuery('body').append(modal);

        modal.find('#wldcfwc-tooltip-modal-close-link').on('click',close_modal);
        modal.find('#wldcfwc-tooltip-modal-continue-link').on(
            'click',function () {
                continueCallback();
                close_modal();
            }
        );

        var parent = button_obj;
        var parentCoords = parent.getBoundingClientRect(), left, top;

        var left = parseInt(parentCoords.left) + ((parent.offsetWidth - modal.width()) / 2);
        var top = parseInt(parentCoords.bottom) - 100 + window.pageYOffset;
        modal.css({position: 'absolute',left: left.toFixed(0) + 'px',top: top.toFixed(0) + 'px'}).fadeIn(200);

        setTimeout(
            function () {
                addOutsideModalClick(modal);
            },500
        );

    }
    function addSpinnerToButton(button)
    {
        var this_button = button;
        let spinner = this_button.querySelector('.wldcfwc_spinner-icon');
        if (!spinner) {
            spinner = document.createElement('div');
            spinner.className = 'wldcfwc_spinner-icon';
            this_button.appendChild(spinner);
        }
        // Show the spinner
        spinner.style.display = 'inline-block';
        setTimeout(
            function () {
                spinner.style.display = 'none';
            },2000
        );
    }
    function buttonClickRun(button_event)
    {

        if(wldcfwc_show_add2wishlist_button_spinner == 'yes_str') {
            addSpinnerToButton(button_event.target);
        }

        var button_obj = button_event.target;
        var parameters = parseButtonObjParameters(button_obj)
        if(parameters['prevent_default']) {
            //it's an <a> tag with event listener and href='#'
            button_event.preventDefault();
        }

        //sets global var wishData
        getProductData(button_obj, parameters);

        if(wishData.wish_data) {
            var multiple_products = false;
            var is_variable = false;
            var variation_found = false;
            if(Array.isArray(wishData.wish_data) && wishData.wish_data.length > 1) {
                //multiple products from a list
                multiple_products = true;
            }else{
                //single prod
                data = JSON.parse(wishData.wish_data);
                if(data.product_type && data.product_type == 'variable') {
                    //single prod with options that need to be selected
                    is_variable = true;
                }
                if(is_variable && data.variation_id) {
                    variation_found = true;
                }
            }
            if(is_variable && !variation_found) {
                //single prod with options that need to be selected
                showModalWithCancelAndContinue(
                    button_obj,function () {
                        //this function is the skip button callback
                        add2WishList();
                    }
                );
            }else{
                add2WishList();
            }
        }
    }
    function add2WishList(button_event)
    {

        //this is where Content-Security-Policy could throw error due to appendChild
        createWishListDotComDiv();

        //open window with name of add2WishListComPopWin. use window open here so it's a user action and a pop window won't be blocked x seconds later if there's an error
        if(typeof wldcfwc_add2wishlist_window != 'undefined' && wldcfwc_add2wishlist_window == 'popwindow') {
            openWindow();
            submitForm('add2WishListComPopWin');
        }else {
            submitForm('_self');
        }
    }
    function init_tooltip(selector)
    {
        var hovering = false;
        function onmouseLeave(e)
        {
            hovering = false;
            var elm = jQuery(this);
            var tooltip = jQuery('body').find('.wldcfwc-tooltip-selector');
            setTimeout(
                function () {
                    if(elm.attr('data-has-hover') == 0) {
                        closeTooltip();
                    }
                },700
            );
            elm.attr('data-has-hover',0);
        }
        function closeTooltip(e)
        {
            if(!hovering) {
                var tooltip = jQuery('body').find('.wldcfwc-tooltip-selector');
                tooltip.fadeOut(
                    200, function () {
                        tooltip.remove();
                    }
                );
            }
        }
        function onmouseEnter(e)
        {
            hovering = true;
            var elm = jQuery(this), tooltip = null,
            text = '',
            mywishlistlink = "<a href='"+wooCommApi_mywishlist_ui+"'>My wishlist</a>";
            elm.attr('data-has-hover',1);
            var tooltip_exists = jQuery('body').find('.wldcfwc-tooltip-selector');
            if(tooltip_exists.length) {
                tooltip_exists.remove();
            }
            text = elm.data('wldcfwc-tooltip-title');

            if(text && text.length > 0) {

                text = text.replace(' Powered by WishList.com','<br>Powered by WishList.com');

                var logo_html = "<img class=\"wldcfwc_logo-icon-inline-text wldcfwc_powered-by-wishlist-com\" src=\""+wldcfwc_wl_icon_url+"\">";
                text = text.replace(' WishList.com',' '+logo_html+'WishList.com');

                if(typeof wldcfwc_wl_hp_url_path != 'undefined' && document.location.href.indexOf(wldcfwc_wl_hp_url_path) != -1 ) {
                    //don't show my wishlist on wishlist homepage tooltip
                    tooltip = jQuery("<div id='wldcfwc_tooltip_id' class='wldcfwc-tooltip wldcfwc-tooltip-selector'>"+text+"</div>");
                }else{
                    tooltip = jQuery("<div id='wldcfwc_tooltip_id' class='wldcfwc-tooltip wldcfwc-tooltip-selector'>"+text+"<br>"+mywishlistlink+"</div>");
                }

                tooltip.on(
                    'mouseover',function () {
                        elm.attr('data-has-hover',1);
                    }
                );
                tooltip.on(
                    'mouseout',function () {
                        //this is mouseout for tooltip and button because tooltip span is inside button
                        elm.attr('data-has-hover',0);
                    }
                );

                jQuery('body').append(tooltip);

                tooltip.on('mouseleave',closeTooltip);

                var parent = e.target;
                //svg conflicts with the coordinates
                if(parent instanceof SVGElement) {
                       //get the svg's parent
                       parent = parent.parentNode;
                }
                var parentCoords = parent.getBoundingClientRect(), left, top;

                var left = parseInt(parentCoords.left) + ((parent.offsetWidth - tooltip.width()) / 2);
                var top = parseInt(parentCoords.bottom) + 10 + window.pageYOffset;
                tooltip.css({position: 'absolute',left: left.toFixed(0) + 'px',top: top.toFixed(0) + 'px'}).fadeIn(200);
            }else{
            }

        }
        jQuery(selector).each(
            function () {
                var elm = jQuery(this);
                //only for desktop
                if (!('ontouchstart' in window)) {
                    elm.off('mouseenter',onmouseEnter);
                    elm.off('mouseleave',onmouseLeave);
                    elm.off('click',onmouseLeave);

                    elm.on('mouseenter',onmouseEnter);
                    elm.on('mouseleave',onmouseLeave);
                    elm.on('click',onmouseLeave);
                }
            }
        );
    }
    init_tooltip('.wldcfwc_doTooltip');
    function wldcfwc_windowWH()
    {
        var window_wh = [480,550]
        try{
            var minWindowWidth = 480;
            var maxWindowWidth = 790;
            var windowWidth = maxWindowWidth;
            var screenWidth = parseInt(document.documentElement.clientWidth);
            if(screenWidth < maxWindowWidth) {
                windowWidth = parseInt(screenWidth*.75);
            }else if(windowWidth < minWindowWidth) {
                windowWidth = minWindowWidth;
            }
            var minWindowHeight = 480;
            var maxWindowHeight = 880;
            var windowHeight = maxWindowHeight;
            var screenHeight = parseInt(document.documentElement.clientHeight);
            if(screenHeight < maxWindowHeight) {
                windowHeight = parseInt(screenHeight*.85);
            }else if(windowHeight < minWindowHeight) {
                windowHeight = minWindowHeight;
            }
            var window_wh = [windowWidth,windowHeight]
        }catch (e)
        {
            //
        }
        return window_wh
    }
    function updateWCProductVariant(event,variation)
    {
        if(variation.variation_id) {
            //var product_id_field = event.target.querySelector('[name="product_id"]');
            var product_id = event.target.getAttribute('data-product_id');
            if(product_id) {
                var data_span_id = 'wldcfwc_data_span_'+product_id;
                var data_elm = document.getElementById(data_span_id);
                if(data_elm) {
                    if(variation.variation_id) {
                        data_elm.setAttribute('data-wldcfwc-selected-prod-id',variation.variation_id);
                    }else {
                        data_elm.setAttribute('data-wldcfwc-selected-prod-id',product_id);
                    }
                    var data = data_elm.getAttribute('data-wldcfwc-wish');
                    data = JSON.parse(data);

                    var attr_params = [];
                    for(const key in variation.attributes ){
                        attr_params.push(key+'='+variation.attributes[key]);
                    }
                    if(attr_params.length) {
                        var url_attr_params_str = attr_params.join('&');
                        var desc_attr_params_str = attr_params.join(', ');
                        desc_attr_params_str = desc_attr_params_str.replace(/attribute_pa_/g,'');
                        desc_attr_params_str = desc_attr_params_str.replace(/=/g,': ');
                    }
                    data.variation_id = variation.variation_id;
                    var wlurl_a = data.wlurl.split('?');
                    data.variation_wlurl = wlurl_a[0]+'?'+url_attr_params_str;
                    data.variation_wlburl = wlurl_a[0]+'?add-to-cart='+variation.variation_id;
                    data.variation_wlprice = variation.display_price;
                    data.variation_wliurl = variation.image.url;
                    data.variation_wlsku = variation.sku;
                    data.variation_wlpname = data.wlpname + ', '+desc_attr_params_str;
                    data.variation_wldesc = data.wldesc + ', '+desc_attr_params_str;
                    data.wlgift_notes = desc_attr_params_str;

                    var wish_data_esc = JSON.stringify(data);
                    data_elm.setAttribute('data-wldcfwc-wish',wish_data_esc);
                }
            }
        }
    }
    function clearWCProductVariant(event,variation)
    {
        event.target.disabled = true;
        var product_id = event.target.getAttribute('data-product_id');
        if(product_id) {
            var data_span_id = 'wldcfwc_data_span_'+product_id;
            var data_elm = document.getElementById(data_span_id);
            if(data_elm) {
                data_elm.setAttribute('data-wldcfwc-selected-prod-id',product_id);
                var data = data_elm.getAttribute('data-wldcfwc-wish');
                data = JSON.parse(data);

                delete data.variation_id;
                delete data.variation_wlurl;
                delete data.variation_wlburl;
                delete data.variation_wlprice;
                delete data.variation_wliurl;
                delete data.variation_wlsku;
                delete data.variation_wlpname;
                delete data.variation_wldesc;
                delete data.wlgift_notes;

                var wish_data_esc = JSON.stringify(data);
                data_elm.setAttribute('data-wldcfwc-wish',wish_data_esc);
            }
        }
    }
    function getProductDataFromProductId(product_id)
    {
        var data_span_id = 'wldcfwc_data_span_'+product_id;
        var data_elm = document.getElementById(data_span_id);
        var data_final = {};
        if(data_elm) {
            var data = data_elm.getAttribute('data-wldcfwc-wish');
            var data_final = data;
        }
        return data_final;
    }
    function getProductData(button_obj, parameters)
    {
        if(!parameters) {
            var parameters = parseButtonObjParameters(button_obj);
        }
        var product_id;
        if(parameters.hasOwnProperty('product_id')) {
            var button_type = 'product';
            var data_final = getProductDataFromProductId(parameters['product_id']);
            product_id = parameters['product_id'];
        }else if(parameters.hasOwnProperty('select_list_items')) {
            button_type = 'list';
            var data = [];
            //add2wishlist whole WishList button
            var data_elm_collection = document.getElementsByClassName('wldcfwc-data-span');
            for(var i=0; i<data_elm_collection.length; i++){
                var data_elm = data_elm_collection[i];
                var this_data = data_elm.getAttribute('data-wldcfwc-wish');
                data[i] = this_data;
            };
            var data_final = data;
        }
        setWishData(data_final,button_type,product_id);
    }
    function setWishData(data_final,button_type,product_id)
    {
        //save data
        wishData.wish_data = data_final;

        //save parameters
        wishData.button_type = button_type;
        wishData.is_hidden_post = 0;
        wishData.auto_save_wish = 0;
        wishData.input_type = input_type;

        if(product_id) {
            wishData.product_id = product_id;
        }

    }
    function parseButtonParameters(button_parameters_str)
    {
        var parameters={},param,val,button_parameters;
        if(button_parameters_str) {
            button_parameters = button_parameters_str.split(',');
            for(var i=0;i<button_parameters.length;i++){
                param = button_parameters[i].substring(0,button_parameters[i].indexOf('='));
                val = button_parameters[i].substring(button_parameters[i].indexOf('=')+1,button_parameters[i].length);
                parameters[param] = val;
            }
        }
        return parameters;
    }
    function parseButtonObjParameters(button_obj)
    {
        var parameters;
        var button_parameters_str = button_obj.getAttribute('data-wldcfwc-button-parameters');
        if(!button_parameters_str) {
            //try parent
            button_obj = button_obj.parentElement;
            button_parameters_str = button_obj.getAttribute('data-wldcfwc-button-parameters');
        }
        if(!button_parameters_str) {
            //try parent with closest
            button_obj = button_obj.closest('[data-wldcfwc-button-parameters]');
            button_parameters_str = button_obj.getAttribute('data-wldcfwc-button-parameters');
        }

        parameters = parseButtonParameters(button_parameters_str);

        //add flag to preventDefault
        if(button_obj.getAttribute('href') && (button_obj.getAttribute('href') === '#' || button_obj.getAttribute('href').indexOf('void') != -1  )) {
            //it's an <a> tag with event listener.
            parameters['prevent_default'] = true;
        }else{
            parameters['prevent_default'] = false;
        }

        return parameters;
    }
    function createElement(doc, str)
    {
        try{
            var elem = doc.standardCreateElement(str);
            if (typeof(elem)=="object") {
                return elem;
            }
        }
        catch(e){}

        return doc.createElement(str);
    }
    function createWishListDotComDiv()
    {
        //see dynamically created div already exists
        var div_id = "wldcfwc_add2wishlist_div"
        var check_wldcfwc_div = document.getElementById(div_id);
        if (check_wldcfwc_div != null) {
            check_wldcfwc_div.parentElement.removeChild(check_wldcfwc_div)
        }
        //create outer div to hold innerHTML. need to set outer divs style here
        var wldcfwc_div = createElement(document,"div");
        wldcfwc_div.id = div_id;
        wldcfwc_div.style.display = 'none';
        document.body.appendChild(wldcfwc_div);

        var innerHTML = formHTML;
        wldcfwc_div.innerHTML = innerHTML;
    }
    function openWindow(url)
    {
        var wh_a = wldcfwc_windowWH();
        var windowWidth = wh_a[0];
        var windowHeight = wh_a[1];
        if(typeof url == "undefined") {
            //some sites monkey patch window.open and don"t allow empty url
            url = blank_url;
        }

        if(typeof wishlist_popwind_obj != 'undefined') {
            //this will force open below to bring the window into focus
            try {
                wishlist_popwind_obj.close();
            }catch(e){
            }
        }

        //chrome ignores window parameters and uses non-editable address bar with no buttons
        wishlist_popwind_obj = window.open(url,"add2WishListComPopWin","location=1,menubar=1,width="+windowWidth+",height="+windowHeight+",top=50,scrollbars=1,toolbar=1");
        //popwin = window.open(url,"add2WishListComPopWin");
        if(typeof wishlist_popwind_obj != 'undefined') {
            try {
                //this doesn't work in all cases, but the close logic above
                wishlist_popwind_obj.focus();
            }catch(e){
            }
        }
    }
    function submitForm(target)
    {
        var form_obj = document.getElementById("wldcfwc_form");
        if(target) {
            form_obj.target = target;
        }else{
            form_obj.target = 'add2WishListComPopWin';
        }
        if(form_obj.target.toLowerCase().indexOf('popwin') != -1) {
            document.forms["wldcfwc_form"].action = add2wishlistUrl_popwin_ui;
        }else{
            document.forms["wldcfwc_form"].action = add2wishlistUrl_ui;
        }
        form_obj.elements['input_type'].value = wishData.input_type;
        form_obj.elements['is_popwin'].value = 1;
        form_obj.elements['is_hidden_post'].value = wishData.is_hidden_post;
        form_obj.elements['auto_save_wish'].value = wishData.auto_save_wish;
        form_obj.elements['return_url'].value = return_url;
        form_obj.elements['product_id'].value = wishData.product_id;

        //if wishData.wish_data is an array, it becomes a comma separated list of objects
        form_obj.elements['wish_data'].value = wishData.wish_data.toString();

        //window needs to already be open
        form_obj.submit();
    }
    function buttonClickStart(button_event)
    {
        try
        {
            buttonClickRun(button_event);
        }
        catch (e)
        {
            console.log(e);
        }
    }
    function addButtonListeners()
    {
        //add2wishlist product buttons
        var Add2WishList_button,has_listener;
        var Add2WishList_buttons_collection = document.querySelectorAll('[data-wldcfwc-button-parameters]');
        for(var i=0; i<Add2WishList_buttons_collection.length; i++){
            Add2WishList_button = Add2WishList_buttons_collection[i];
            Add2WishList_button.removeEventListener('click', buttonClickStart);
            Add2WishList_button.addEventListener('click', buttonClickStart);
        };
    }
    addButtonListeners();
    //product variant selected
    //see wp-content/plugins/woocommerce/includes/class-wc-shortcodes.php for event trigger
    jQuery('form.variations_form').on(
        'found_variation', function ( event, variation ) {
            updateWCProductVariant(event, variation);
        }
    );
    //product variant deselected
    jQuery('form.variations_form').on(
        'reset_data', function ( event, variation ) {
            clearWCProductVariant(event, variation);
        }
    );
    //Woocommerce cart updated
    jQuery('body').on(
        'updated_wc_div', function ( event, variation ) {
            addButtonListeners();
        }
    );

    //observe key areas of the screen to see if new add to WishList buttons have been insertd via ajax
    // Select the nodes that will be observed for mutations to support product load via ajax
    var all_wishlist_buttons = document.querySelectorAll('[data-wldcfwc-button-parameters]');
    var wishlist_button_count = all_wishlist_buttons.length;
    var targetNodes = document.querySelectorAll('.products');
    // Options for the observer (which mutations to observe)
    var config = { childList: true, subtree: true };
    // mutationCallback function to execute when mutations are observed
    var mutationCallback = function (mutationsList, observer) {
        //get current count of WishList buttons
        var all_wishlist_buttons_new = document.querySelectorAll('[data-wldcfwc-button-parameters]');
        var wishlist_button_count_new = all_wishlist_buttons_new.length;

        var do_init = false;
        var mutated = false;
        for(var mutation of mutationsList) {
            if (mutation.type == 'childList' || mutation.type == 'subtree') {
                mutated = true;
            }
        }
        if(mutated && wishlist_button_count_new > wishlist_button_count) {
            do_init = true;
        }
        if(do_init) {
            //add listeners to the new add to WishList buttons
            wldcfwc_init();
        }
        //save count for next time
        wishlist_button_count = wishlist_button_count_new;
    };
    // Create an instance of the observer with the mutationCallback function
    var mutationObserver = new MutationObserver(mutationCallback);
    // Start observing each target node for configured mutations
    targetNodes.forEach(
        function (node) {
            mutationObserver.observe(node, config);
        }
    );
    function createUrlQsParamJS(param_name,param_val)
    {
        param_val = param_val.replace('?','__qm__');
        param_val = param_val.replace('&','__amp__');
        param_val = param_val.replace('=','__eq__');
        param_val = param_val.replace('%','__per__');
        param_val = param_val +'__'+param_name+'__end';
        return param_val;
    };
    function getUrlQsParamJS(param_name,qs)
    {
        var param_val = qs;
        const fs = param_name + "=";
        const fe = param_name + "__end";
        var startPos = qs.indexOf(fs) + fs.length;
        var endPos = qs.indexOf(fe, startPos);
        if(endPos == -1) {
            endPos = qs.length;
        }
        if (startPos > fs.length - 1) {
            param_val = qs.substring(startPos, endPos);
        }
        param_val = param_val.replace('__qm__','?');
        param_val = param_val.replace('__amp__','&');
        param_val = param_val.replace('__eq__','=');
        param_val = param_val.replace('__per__','%');

        return param_val;
    };
    function keyValUpdateQueryStringParam(url,key,value)
    {
        //encode prior to calling function?
        if(value && typeof value === 'string') {
            if(value.indexOf('%') == -1) {
                value = encodeURI(value);
            }
        }

        var url_a = parseUrlCommon(url);
        var baseUrl = url_a['base'];
        var qs_parameters = url_a['qs_parameters'];
        var hashIndex = url_a['hashIndex'];


        var outPara = {};
        for(k in qs_parameters){
            var ekey = k;
            var evalue = qs_parameters[k];
            //add this to array of exisiting params if it doesn't match param passed
            if(ekey != key) {
                outPara[ekey] = evalue;
            }
        }

        if(value!==undefined && value!==null && value!=='null' && value!=='') {
            //add new param to existing params
            outPara[key] = value;
        }else{
            //remove param from url if it's null
            delete outPara[key];
        }
        parameters = [];
        for(var k in outPara){
            parameters.push(k + '=' + outPara[k]);
        }

        var finalUrl = baseUrl;

        if(parameters.length>0) {
            finalUrl += '?' + parameters.join('&');
        }

        var output_url = finalUrl + url.substring(hashIndex);

        return output_url;
    }
    function parseUrlCommon(url)
    {
        var hashIndex = url.indexOf("#")|0;
        if (hashIndex === -1) { hashIndex = url.length|0;
        }
        var urls = url.substring(0, hashIndex).split('?');

        var base = urls[0];
        var qs = '';
        var parameters = '';
        var outPara = {};
        if(urls.length>1) {
            var qs = urls[1];
            parameters = urls[1];
        }
        if(parameters!=='') {
            //these are existing params
            parameters = parameters.split('&');
            for(k in parameters){
                var keyVal = parameters[k];
                keyVal = keyVal.split('=');
                var ekey = keyVal[0];
                var evalue = '';
                if(keyVal.length>1) {
                    evalue = keyVal[1];
                }
                outPara[ekey] = evalue;
            }
        }
        return {'base':base,'qs':qs,'qs_parameters':outPara,'hashIndex':hashIndex}
    }
    function getUrlParameter(param)
    {
        param = param.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + param + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    };
    function removeUrlParameters(url,params)
    {
        // Create a URL object based on the current document's URL
        const current_url = new URL(url);

        // Access the URL's search parameters
        const search_params = current_url.searchParams;

        // Loop through the array of parameters to remove
        params.forEach(
            param => {
                // Remove the parameter if it exists
                search_params.delete(param);
            }
        );

        // Rebuild the URL with the modified search parameters
        const new_url = current_url.toString();

        return new_url;
    }
    function checkLoginQSFlag(){
        var has_login = getUrlParameter('wldcfwc_hli');
        if(has_login == 'yes'){
            setCookie('wldcfwc_user_logged_in','yes',1);
        }else if(has_login == 'no'){
            document.cookie = 'wldcfwc_user_logged_in=; max-age=-99999999;path=/';
        }
    }
    checkLoginQSFlag();
    function updateWLMenu(){
        var menu_item,orig_href;
        var allElements = document.querySelectorAll('a');
        var wldcfwc_user_logged_in = getCookie('wldcfwc_user_logged_in');
        if(wldcfwc_wl_hp_url_path){
            allElements.forEach(element => {
                if(element.href.indexOf(wldcfwc_wl_hp_url_path) != -1){
                    menu_item = element;
                    var data_orig_href = menu_item.getAttribute('data-orig-href');
                    var orig_href = menu_item.href;
                    if(!data_orig_href){
                        menu_item.setAttribute('data-orig-href', orig_href);
                    }
                    var updated_href = orig_href;
                    if(wldcfwc_user_logged_in != 'yes'){
                        //add flag to route to mywishlist which will return if not logged in. see wldcfwc_redirect_wishlist_hp_to_mywishlist()
                        if(orig_href.indexOf('mywishlist') != -1){
                            //do nothing
                        }else if (updated_href.indexOf('?') !== -1) {
                            updated_href += '&mywishlist=1';
                        } else {
                            updated_href += '?mywishlist=1';
                        }
                    }else{
                        updated_href = wooCommApi_mywishlist_ui;
                    }
                    menu_item.setAttribute('href', updated_href);
                }
            });
        }
    }
    updateWLMenu();
    function doWooAPIAjaxCall(data)
    {
        return fetch(
            wldcfwc_api_settings.root + 'wldcfwc_wishlistdotcom/v1/wldcfwc_api/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', // Indicate sending JSON data
                    'X-WP-Nonce': wldcfwc_api_settings.nonce // Pass nonce for WP REST API security
                },
                body: JSON.stringify(data)
            }
        )
        .then(
            response => {
                if (!response.ok) {
                    throw new Error('wldcfwc_api Network response was not ok ' + response.statusText);
                }
                return response.json(); // Parse JSON response into native JavaScript objects
            }
        );
    }
}

document.addEventListener(
    "DOMContentLoaded", function (e) {
        wldcfwc_init();
    }
);