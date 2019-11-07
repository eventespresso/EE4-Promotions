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
        form_input:    {},
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
            var container = $('#ee-spco-payment_options-reg-step-form-payment-options-before-payment-options');
            if (container.length) {
                PROMO.container = container;
                PROMO.adjust_input_and_submit_button_css();
                PROMO.set_listener_for_form_input();
            }
        },

        /**
         * @function
         */
        adjust_input_and_submit_button_css: function() {
            if (PROMO.display_debug) {
                console.log();
                console.log(JSON.stringify('@PROMO.adjust_input_and_submit_button_css()', null, 4));
            }
            PROMO.form_label     = $('#ee-promotion-code-input-lbl');
            PROMO.form_input     = $('#ee-promotion-code-input');
            PROMO.form_submit    = $('#ee-promotion-code-submit');
            var submit_width     = PROMO.form_submit.outerWidth();
            var half_label_width = PROMO.form_label.outerWidth() / 2;
            if (half_label_width > submit_width && half_label_width > 100) {
                var form_label = PROMO.form_label.position();
                PROMO.form_input.addClass('ee-promo-combo-input').css({
                    'width':  (PROMO.container.outerWidth() - submit_width),
                    'top':    form_label.top,
                    'height': PROMO.form_submit.outerHeight(),
                });
                PROMO.form_submit.addClass('ee-promo-combo-submit').css({'top': form_label.top});
            }
        },

        /**
         * @function
         */
        set_listener_for_form_input: function() {
            if (PROMO.display_debug) {
                console.log();
                console.log(JSON.stringify('@PROMO.set_listener_for_form_input()', null, 4));
            }
            PROMO.container.on('click', '#ee-promotion-code-submit', function(event) {
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
                    var msg = SPCO.generate_message_object('',
                        SPCO.tag_message_for_debugging('Promotions: set_listener_for_form_input',
                            eei18n.no_promotions_code), '');
                    SPCO.scroll_to_top_and_display_messages(SPCO.main_container, msg, true);
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
            PROMO.form_data.action     = 'submit_promo_code';
            PROMO.form_data.promo_code = promo_code;
            PROMO.submit_ajax_request();
        },

        /**
         * @function
         * @param  {object} response
         */
        update_payment_info_table: function(response) {
            if (PROMO.display_debug) {
                console.log();
                console.log(JSON.stringify('@PROMO.update_payment_info_table()', null, 4));
            }
            var $spco_payment_info_table = $('#spco-payment-info-table');
            $spco_payment_info_table.find('tbody').html(response.return_data.payment_info);
            SPCO.scroll_to_top_and_display_messages(SPCO.main_container, response, true);
            if (typeof response.return_data.cart_total !== 'undefined') {
                var payment_amount = parseFloat(response.return_data.cart_total);
                SPCO.main_container.trigger('spco_payment_amount', [payment_amount]);
                if (payment_amount === 0) {
                    var $next_step_btn = $spco_payment_info_table.closest('form').find('.spco-next-step-btn');
                    if (PROMO.display_debug) {
                        console.log(JSON.stringify('payment_amount === 0', null, 4));
                        console.log(JSON.stringify('> trigger click on #' + $next_step_btn.attr('id'), null, 4));
                    }
                    SPCO.enable_submit_buttons();
                    $next_step_btn.trigger('click');
                }
            }
        },

        /**
         *  @function
         */
        submit_ajax_request: function() {
            // no form_data ?
            if (typeof PROMO.form_data.action === 'undefined' || PROMO.form_data.action === '') {
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
                SPCO.console_log_object('PROMO.form_data', PROMO.form_data, 0);
            }

            // send AJAX
            $.ajax({
                type:       'POST',
                url:        eei18n.ajax_url,
                data:       PROMO.form_data,
                dataType:   'json',
                beforeSend: function() {
                    SPCO.do_before_sending_ajax();
                },
                success:    function(response) {
                    PROMO.process_response(response);
                },
                error:      function() {
                    SPCO.ajax_request_server_error();
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

                if (PROMO.display_debug) {
                    SPCO.console_log_object('PROMO.response', response, 0);
                }

                if (typeof response.errors !== 'undefined') {
                    // no response...
                    //SPCO.hide_notices();
                    SPCO.scroll_to_top_and_display_messages(SPCO.main_container, response, true);
                } else if (typeof response.attention !== 'undefined') {
                    // Achtung Baby!!!
                    SPCO.scroll_to_top_and_display_messages(SPCO.main_container, response, true);
                } else if (typeof response.success !== 'undefined') {
                    SPCO.scroll_to_top_and_display_messages(SPCO.main_container, response, true);
                } else if (typeof response.return_data !== 'undefined') {

                    if (typeof response.return_data.payment_info !== 'undefined') {
                        PROMO.update_payment_info_table(response);
                    }

                } else {
                    // oh noes...
                    SPCO.ajax_request_server_error();
                }

            } else {
                SPCO.ajax_request_server_error();
            }
        },



        // end of PROMO object
    };

    SPCO.main_container.on('spco_display_step', function(event, step_to_show) {
        if (typeof step_to_show !== 'undefined' && step_to_show === 'payment_options') {
            PROMO.initialize();
        }
    });

    PROMO.initialize();

});
