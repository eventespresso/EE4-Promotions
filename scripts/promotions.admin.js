var PROMO;
jQuery(document).ready(function($) {

    /**
     * @namespace PROMO
     * @type {{
		 *     container: object,
		 *     form_input: object,
		 *     form_data: object,
		 *     entered_codes: object,
		 *     display_debug: number,
	 * }}
     * @namespace form_data
     * @type {{
		 *     action: string,
		 *     promo_code: string,
		 *     noheader: boolean,
		 *     ee_front_ajax: boolean,
		 *     EESID: string,
	 * }}
     * @namespace eei18n
     * @type {{
		 *     EESID: string,
		 *     ajax_url: string,
		 *     wp_debug: boolean,
		 *     no_promotions_code: string
		 * }}
     * @namespace response
     * @type {{
		 *     errors: string,
		 *     attention: string,
		 *     success: string,
		 *     return_data: object,
		 *     payment_info: string,
		 *     promo_accepted: boolean
		 * }}
     * @namespace return_data
     * @type {{
		 *     payment_info: string,
		 *     cart_total: number
		 * }}
     */
    PROMO = {

        // main promotions container
        container:     {},
        // promotions text input label
        form_label:    {},
        // promotions text input field
        form_input:    $('#ee-promotion-code-input'),
        // promotions submit button
        form_submit:   {},
        // array of form data
        form_data:     {},
        // array of input fields that require values
        entered_codes: [],
        // display debugging info in console?
        display_debug: eei18n.wp_debug,

        /********** INITIAL SETUP **********/



        /**
         * @function
         */
        initialize: function() {
            if (PROMO.display_debug) {
                console.log();
                console.log(JSON.stringify('@PROMO.initialize()', null, 4));
            }
            
			PROMO.set_listener_for_form_input();
        },

        /**
         * @function
         */
        set_listener_for_form_input: function() {
            if (PROMO.display_debug) {
                console.log();
                console.log(JSON.stringify('@PROMO.set_listener_for_form_input()', null, 4));
            }
            $(document).on('click', '#ee-promotion-code-submit', function(event) {
                if (PROMO.display_debug) {
                    console.log(JSON.stringify('>> CLICK << on #ee-promotion-code-submit', null, 4));
                }
                event.preventDefault();
                event.stopPropagation();
                var promo_code = PROMO.form_input.val();
                if (typeof promo_code !== 'undefined' && promo_code !== '') {
                    if (PROMO.display_debug) {
                        console.log(JSON.stringify('promo_code: ' + promo_code, null, 4));
                    }
                    PROMO.submit_promo_code(promo_code);
                } else {
					/*
					@TODO: no promo code
                    var msg = SPCO.generate_message_object('',
                        SPCO.tag_message_for_debugging('Promotions: set_listener_for_form_input',
                            eei18n.no_promotions_code), '');
					SPCO.scroll_to_top_and_display_messages(SPCO.main_container, msg, true);
					*/
                }
            });
        },

        /**
         *  @function
         *  @param {string} promo_code
         */
        submit_promo_code: function(promo_code) {
            // no code ?
            if (promo_code === '') {
                return;
            }
            if (PROMO.display_debug) {
                console.log();
                console.log(JSON.stringify('@PROMO.submit_promo_code()', null, 4));
            }
            PROMO.form_data            = {};
            PROMO.form_data.action     = 'submit_txn_promo_code';
			PROMO.form_data.promo_code = promo_code;
			PROMO.disable_button();
            PROMO.submit_ajax_request();
        },

        /**
         *  @function
         */
        submit_ajax_request: function() {
            // no form_data ?
            if (typeof PROMO.form_data.action === 'undefined' || PROMO.form_data.action === '') {
				PROMO.enable_button();
                return;
            }
            if (PROMO.display_debug) {
                console.log();
                console.log(JSON.stringify('@PROMO.submit_ajax_request()', null, 4));
            }
            PROMO.form_data.action        = 'espresso_' + PROMO.form_data.action;
            PROMO.form_data.noheader      = 1;
            PROMO.form_data.ee_front_ajax = 1;
            PROMO.form_data.EESID         = eei18n.EESID;

            if (PROMO.display_debug) {
               console.log( PROMO.form_data );
            }

            // send AJAX
            $.ajax({
                type:       'POST',
                url:        eei18n.ajax_url,
                data:       PROMO.form_data,
                dataType:   'json',
                success:    function(response) {
					PROMO.process_response(response);
					PROMO.enable_button();
                },
                error:      function() {
					/*
					@TODO error
					*/
					PROMO.enable_button();
                },
            });
        },

        /**
         * @function
         * @param  {object} response
         */
        process_response: function(response) {
            if (PROMO.display_debug) {
                console.log();
                console.log(JSON.stringify('@PROMO.process_response()', null, 4));
            }

            PROMO.form_input.val('');
            if (typeof response !== 'undefined' && response !== null) {

                if (typeof response.errors !== 'undefined') {
                    // no response...
                    //SPCO.hide_notices();
                    // @TODO (SPCO.main_container, response, true);
                } else if (typeof response.attention !== 'undefined') {
                    // Achtung Baby!!!
                    // @TODO SPCO.scroll_to_top_and_display_messages(SPCO.main_container, response, true);
                } else if (typeof response.success !== 'undefined') {
                    // @TODO SPCO.scroll_to_top_and_display_messages(SPCO.main_container, response, true);
                } else if (typeof response.return_data !== 'undefined') {

                    if (typeof response.return_data.payment_info !== 'undefined') {
                        // @TODO => success!!!
                    }

                } else {
					// oh noes...
					/*
					@TODO AJAX errors
					SPCO.ajax_request_server_error();
					*/
                }

            } else {
                /*
				@TODO AJAX errors
				SPCO.ajax_request_server_error();
				*/
            }
		},
		
		/**
         * @function
         */
        enable_button: function() {
            $('#ee-promotion-code-submit').removeClass('disabled');
        },
		
		/**
         * @function
         */
        disable_button: function() {
            $('#ee-promotion-code-submit').addClass('disabled');
        },

        // end of PROMO object
    };

    PROMO.initialize();

});
