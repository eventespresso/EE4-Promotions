jQuery(document).ready(function($){

	/**
	 * Object that has all the helper methods for the promotions admin requirements.
	*/
	var eePromotionsHelper = {

		/**
		 * cache for scope slug current lin use.
		 *
		 * @type {string}
		*/
		scopeSlug : '',




		/**
		 * returns the scope slug for the promotion scope that is currently being interacted with.
		 *
		 * @return {string}
		 */
		getScope: function() {
			if (  this.scopeSlug  !== '' )
				return this.scopeSlug;
			this.scopeSlug = $('.ee-promotions-applies-to-main-container').attr('id').replace('ee-promotions-applies-to-', '');
			return this.scopeSlug;
		},


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
			}
			ts = ts + String(num);
			for(i=0;i<ts.length;i++) {
				out+=Number(ts.substr(i, 2)).toString(36);
			}
			return (out);
		},



		/**
		 * Used to toggle whether a scope item is selected or deselected.
		 *
		 * @param {string} selected the checkbox toggled
		 *
		 * @return {eePromotionsHelper}
		 */
		scopeItemToggle: function (selected) {
			if ( typeof(selected) === 'undefined' ) {
				console.log(eei18n.toggledScopeItemMissingParam);
				return false;
			}

			var checkeditem = $(selected), curItems, itemsInput;

			//selected or deselected?
			var isSelected = $(selected).is(':checked');
			itemsInput = $('#ee-selected-items-' + this.getScope() );
			curItems = itemsInput.val().split(',');
			if ( isSelected ) {
				//adding item to hidden elements
				curItems.push($(selected).val());
			} else {
				curItems = $().removeFromArray(curItems, $(selected).val());
			}

			itemsInput.val( curItems.join(',').replace(/^,|,$/,'') );
			return this;
		},



		/**
		 * The method is used to get scope items for selection based on the indicated
		 * filters and sort.  This is done more dynamically because different scopes may
		 * have different filters set.
		 *
		 * @return {eePromotionsHelper}
		 */
		getScopeSelectionItems: function() {
			var data={};
			//get selections from filters
			$('select', '.ee-promotions-applies-to-filters').each( function(i) {
				data[$(this).attr('name')] = $(this).val();
			});
			$('input', '.ee-promotions-applies-to-filters').each( function(i) {
				data[$(this).attr('name')] = $(this).val();
			});

			//what's the sort set at?
			data.PRO_scope_sort = $('#ee-promotion-items-sort-order').text();

			//what's the display only selected set at?
			data.PRO_order_by_selected = $('#ee-display-selected-trigger').val();

			//what about paging?
			data.paged = $('.current-page', '.ee-promotions-applies-to-paging').val();
			data.perpage = 10; //@todo this should be a value that can be set by user.

			//alright all the data is setup now let's set what we want to do on ajax success.
			$(document).ajaxSuccess( function(event, xhr, ajaxoptions )  {
				//we can get the response from xhr
				var ct = xhr.getResponseHeader( "content-type" ) || "";
				if ( ct.indexOf('json') > -1 ) {
					var resp = xhr.responseText;
					resp = $.parseJSON(resp);
					//let's replace the current items in the selected items window.
					$('.ee-promotions-applies-to-items-container', '.ee-promotions-applies-to-selector').html(resp.content);
				}
			});

			//action
			data.action = 'promotion_scope_items';

			//do ajax
			this.doAjax(data);
		},



		/**
		 * Handles ajax requests.
		 * NOTE: this does NOT handle any success actions.  It's up to the caller to set
		 * what is expected on success.
		 *
		 * @param {object} data The data to go with the ajax package
		 *
		 * @return {void}
		 */
		doAjax: function(data) {
			data.ee_admin_ajax = true;
			data.page = typeof(data.page) === 'undefined' ? 'espresso_promotions' : data.page;/**/

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data
			});
		}

	};



	/**
	 * trigger for generating coupon code.
	 */
	$('#promotion-details-form').on('click', '#generate-promo-code', function(e) {
		e.preventDefault();
		e.stopPropagation();
		eePromotionsHelper.generate_code('#PRO_code');
	});



	/**
	 * trigger for toggling the selection of a scope item
	 */
	$('.promotion-applies-to-items-ul').on('click', ':checkbox', function(e) {
		e.stopPropagation();
		eePromotionsHelper.scopeItemToggle(this);
	});



	/**
	 * Date and time picker trigger
	 */
	$('#post-body').on('focusin', '.ee-datepicker', function(e) {
		e.preventDefault();
		var data= $(this).data();
		var container = data.container == 'main' ? '#promotion-details-mbox' : '#promotions-applied-to-mbox';
		var start = data.context == 'start' ? $(this, container) : $('[data-context="start"]', container);
		var end = data.context == 'end' ? $(this, container) : $('[data-context="end"]', container );
		var next = $(data.nextField, 'container');
		var doingstart = data.context == 'start' ? true : false;
		dttPickerHelper.resetpicker().setDefaultDateRange('months', 1).picker(start, end, next, doingstart);
	});


	/**
	 * trigger for toggling the selection of ALL promotion applies to items.
	 */
	$('.ee-promotions-applies-to-selector').on('click', '.ee-select-all-trigger', function(e) {
		e.stopPropagation();
		$(':checkbox', '.promotion-applies-to-items-ul').each( function(i) {
			$(this).trigger('click');
		});
	});




	/**
	 * trigger for applying filters to the selected items.
	 */
	$('#post-body').on('click', '#ee-apply-promotion-filter', function(e) {
		e.preventDefault();
		e.stopPropagation();
		eePromotionsHelper.getScopeSelectionItems();
	});

});
