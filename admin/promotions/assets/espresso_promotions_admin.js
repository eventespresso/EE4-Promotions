jQuery(document).ready(function($){

	/**
	 * Object that has all the helper methods for the promotions admin requirements.
	*/
	var eePromotionsHelper = {



		/**
		 * helper method for generating a random unique coupon code.
		 * The generated code will use the content of the given code field for the prefix
		 * and then will replace with the final generated code.
		 *
		 * @param {string} codefield The class or id selector for the field referencing the
		 * code.
		 * @return {eePromotionsHelper}
		*/
		generate_code: function( codefield ) {
			var code = '';
			//make sure we have a selector
			if ( typeof(codefield) === 'undefined' ) {
				console.log( eei18n.codefieldEmptyError );
				return; //get out because there's an error.
			}

			//make sure selector exists
			var field = $(codefield);
			if ( field.length === 0 ) {
				console.log( eei18n.codefieldInvalidError );
				return;
			}

			//made it here?  K let's generate the code if we have a prefix we'll use it.
			var prefix = field.val().length > 0 ? field.val() + '_' : '';
			code = prefix + this.uniqid();
			field.val(code);
			return this;
		},



		/**
		 * Generates a unique alphanumeric id client side using the current Date/time and the host url.
		 *
		 * @return {string}
		 */
		uniqid: function() {
			var ts=String(new Date().getTime()), i = 0, out = '',num=0;
			var host = window.location.host;
			//convert host to num.
			for ( c=0; c < host.length; c++ ) {
				num += host.charCodeAt(c);
			};
			ts = ts + String(num);
			for(i=0;i<ts.length;i++) {
				out+=Number(ts.substr(i, 2)).toString(36);
			}
			return (out);
		}

	};


	$('#promotion-details-form').on('click', '#generate-promo-code', function() {
		eePromotionsHelper.generate_code('#PRO_code');
	});
});
