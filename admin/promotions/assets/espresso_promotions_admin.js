jQuery(document).ready(function($){

	const $postBody = $('#post-body');
	const $promosAppliedToMbox = $('#promotions-applied-to-mbox');
	const $promoDetailsForm = $('#promotion-details-form');

	/**
	 * Object that has all the helper methods for the promotions admin requirements.
	*/
	const eePromotionsHelper = {

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
		 * @return {eePromotionsHelper|void}
		*/
		generate_code: function( codefield ) {
			let code = '';
			//make sure we have a selector
			if ( typeof(codefield) === 'undefined' ) {
				console.log( eei18n.codefieldEmptyError );
				return; //get out because there's an error.
			}

			//make sure selector exists
			const field = $(codefield);
			if ( field.length === 0 ) {
				console.log( eei18n.codefieldInvalidError );
				return;
			}

			//made it here?  K let's generate the code if we have a prefix we'll use it.
			const prefix = $('#PRO_code_prefix' );
			if ( prefix.val() === '' && field.val().length > 0 ) {
				prefix.val( field.val() + '_' );
			}
			code = prefix.val() + this.uniqID();
			field.val(code);
			return this;
		},



		/**
		 * Generates a unique alphanumeric id client side using the current Date/time and the host url.
		 *
		 * @return {string}
		 */
		uniqID: function() {
			const host = window.location.host;
			let i = 0;
			let num = 0;
			let out = '';
			let ts=String(new Date().getTime());
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
		 * @return {eePromotionsHelper|void}
		 */
		scopeItemToggle: function (selected) {
			if ( typeof(selected) === 'undefined' ) {
				console.log(eei18n.toggledScopeItemMissingParam);
				return;
			}

			let curItems;
			let itemsInput;
			let currentCount = $('.ee-promotions-selected-count','.ee-promotions-selected-count-container').text();

			//selected or deselected?
			const isSelected = $(selected).is(':checked');
			itemsInput = $('#ee-selected-items-' + this.getScope() );
			curItems = itemsInput.val().split(',');
			if ( isSelected ) {
				//adding item to hidden elements
				curItems.push($(selected).val());
				currentCount = parseInt( currentCount, 10) + 1;
			} else {
				curItems = $().removeFromArray(curItems, $(selected).val());
				currentCount = parseInt( currentCount, 10 ) - 1;
			}

			itemsInput.val( curItems.join(',').replace(/^,|,$/,'') );
			$('.ee-promotions-selected-count', '.ee-promotions-selected-count-container').text(currentCount);
			return this;
		},




		/**
		 * Used to toggle the sort status for the sort element
		 *
		 * @return {eePromotionsHelper}
		 */
		toggleSort: function() {
			const $promoSortOrder = $('#ee-promotion-items-sort-order');
			const current_sort = $promoSortOrder.text();
			const sortOrder = current_sort === 'ASC' ? 'DESC' : 'ASC';
			const sortClass = current_sort === 'ASC' ? 'dashicons-arrow-down' : 'dashicons-arrow-up';
			let classReplace = current_sort === 'ASC' ? 'dashicons-arrow-up' : 'dashicons-arrow-down';

			//modify sort.
			$promoSortOrder.text(sortOrder);
			classReplace = $('.dashicons', '.ee-sort-container').attr('class').replace(classReplace, sortClass);
			$('.dashicons', '.ee-sort-container').attr('class', classReplace);
			return this;
		},



		/**
		 * The method is used to get scope items for selection based on the indicated
		 * filters and sort.  This is done more dynamically because different scopes may
		 * have different filters set.
		 *
		 * @param {string} page if sent this indicates what the page requested is.
		 * @return {eePromotionsHelper}
		 */
		getScopeSelectionItems: function(page) {
			const data={};

			//make sure the select all box is unchecked
			$('.ee-select-all-trigger', '.ee-promotions-applies-to-selector').prop('checked', false);
			//get selections from filters
			$('select', '.ee-promotions-applies-to-filters').each( function(i) {
				data[$(this).attr('name')] = $(this).val();
			});
			$('input', '.ee-promotions-applies-to-filters').each( function(i) {
				//if item is a checkbox and its not checked, then don't include.
				if ( $(this).attr('type') === 'checkbox' && ! $(this).prop('checked') ) {
					return;
				}
				data[$(this).attr('name')] = $(this).val();
			});

			//what's the sort set at?
			data.PRO_scope_sort = $('#ee-promotion-items-sort-order').text();

			//what's the display only selected set at?
			data.PRO_display_only_selected = $('#ee-display-selected-trigger-'+this.getScope()).prop('checked') ? 1 : 0;

			data.PRO_ID = $('#PRO_ID').val();

			//what about paging?
			data.paged = typeof( page ) !== 'undefined' ? page : $('.current-page', '.ee-promotions-applies-to-paging').val();
			//data.perpage = 10; //@todo this should be a value that can be set by user.

			//make sure we send along any current selected items
			data.selected_items = $('#ee-selected-items-'+this.getScope()).val();

			//alright all the data is setup now let's set what we want to do on ajax success.
			$(document).ajaxSuccess( function(event, xhr, ajaxoptions )  {
				//we can get the response from xhr
				const ct = xhr.getResponseHeader( "content-type" ) || "";
				if ( ct.indexOf('json') > -1 ) {
					const resp = $.parseJSON(xhr.responseText);
					//let's replace the current items in the selected items window.
					$('.ee-promotions-applies-to-items-container', '.ee-promotions-applies-to-selector').html(resp.items_content);
					//update the current page
					$('.ee-promotions-applies-to-paging', '#promotions-applied-to-mbox').html(resp.items_paging);
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
			$('.spinner').css('visibility', 'visible');
			data.ee_admin_ajax = true;
			data.page = typeof(data.page) === 'undefined' ? 'espresso_promotions' : data.page;/**/

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				success: function(response, status, xhr) {
					$('.spinner').css('visibility', 'hidden');
				}
			});
		}

	};

	//reset prefix
	$('#PRO_code_prefix' ).val('');


	/**
	 * trigger for resetting coupon code prefix.
	 */
	$promoDetailsForm.on('click', '#reset-promo-code-prefix', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#PRO_code_prefix' ).val('');
		$('#PRO_code' ).val('');
	});


	/**
	 * trigger for generating coupon code.
	 */
	$promoDetailsForm.on('click', '#generate-promo-code', function(e) {
		e.preventDefault();
		e.stopPropagation();
		eePromotionsHelper.generate_code('#PRO_code');
	});



	/**
	 * trigger for toggling the selection of a scope item
	 */
	$('.ee-promotions-applies-to-items-container').on('click', ':checkbox', function(e) {
		eePromotionsHelper.scopeItemToggle(this);
	});



	/**
	 * Date and time picker trigger
	 */
	$postBody.on('focusin', '.ee-datepicker', function(e) {
		e.preventDefault();
		const data= $(this).data();
		// const container = data.container === 'main' ? '#promotion-details-mbox' : '#promotions-applied-to-mbox';
		const start = data.context === 'start' ? $(this, data.container) : $('[data-context="start"]', data.container);
		const end = data.context === 'end' ? $(this, data.container) : $('[data-context="end"]', data.container );
		const next = $(data.nextField, 'container');
		const doingStart = data.context === 'start';
		dttPickerHelper.resetpicker().setDefaultDateRange('months', 1).picker(start, end, next, doingStart);
	});


	/**
	 * trigger for toggling the selection of ALL promotion applies to items.
	 */
	$('.ee-promotions-applies-to-selector').on('click', '.ee-select-all-trigger', function(e) {
		const selecting = $(this).prop('checked');
		$(':checkbox', '.ee-promotions-applies-to-items-container').each( function(i) {
			if ( $(this).prop('checked') === false && selecting )
				$(this).trigger('click');
			if( $(this).prop('checked') && ! selecting )
				$(this).trigger('click');
		});
	});




	/**
	 * trigger for applying filters to the selected items.
	 */
	$('#ee-apply-promotion-filter').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		eePromotionsHelper.getScopeSelectionItems();
	});



	/**
	 * trigger for display only selected items filter.
	 */
	$promosAppliedToMbox.on('click', '.ee-display-selected-only-trigger', function(e) {
		e.stopPropagation();
		eePromotionsHelper.getScopeSelectionItems();
	});



	/**
	 * trigger for sorts
	 */
	$promosAppliedToMbox.on('click', '.ee-sort-trigger', function(e) {
		e.preventDefault();
		e.stopPropagation();
		eePromotionsHelper.toggleSort().getScopeSelectionItems();
	});


	/**
	 * trigger for paging!
	 */
	$promosAppliedToMbox.on('click', '.pagination-links>a', function(e) {
		e.preventDefault();
		e.stopPropagation();
		const data = parseUri( $(this).attr('href') );
		const paged = typeof( data.queryKey.paged ) !== 'undefined' ? data.queryKey.paged : 1;
		eePromotionsHelper.getScopeSelectionItems(paged);
	});


	/**
	 * capture enter keypress in paging input
	 */
	$promosAppliedToMbox.on('keypress', '.current-page', function(e) {
		if ( e.which === 13 ) {
			e.preventDefault();
			e.stopPropagation();
			const paged = $(this).val();
			eePromotionsHelper.getScopeSelectionItems(paged);
		}
	});


	/**
	 * capture enter keypress in any of the scope filter inputs
	 *
	 */
	$postBody.on('keypress', '.ee-promotions-applies-to-filters>input', function(e){
		if ( e.which === 13 ) {
			e.preventDefault();
			e.stopPropagation();
			eePromotionsHelper.getScopeSelectionItems();
		}
	});


	/**
	 * clear calendar field
	 */
	$postBody.on('click', '.clear-dtt', function(e) {
		e.preventDefault();
		e.stopPropagation();
		const data = $(this).data();
		$(data.field).val('');
	});

    $postBody.on( 'click', '.ee-clear-field', function(e) {
        e.preventDefault();
        e.stopPropagation();
		const data = $(this).data();
        $(data.clearfield).val('');
    });

    $postBody.on( 'click', '.ee-toggle-filters', function(e) {
        e.preventDefault();
        e.stopPropagation();
		const data = $(this).data();
        $(data.filterContainer).slideToggle();
    });

    $postBody.on( 'click', '.ee-toggle-datepicker', function(e) {
        e.preventDefault();
        e.stopPropagation();
		const data = $(this).data();
        $(data.target).trigger('click').focus();
    });

});
