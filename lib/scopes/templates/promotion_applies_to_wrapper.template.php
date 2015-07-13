<?php
/**
 * This file is the template for the promotions applies to metabox wrapper.
 *
 * @since 1.0.0
 * @package EE4 Promotions
 * @subpackage admin
 */
/**
 * Template variables in use for this template
 * @type string $scope_slug		The slug for the scope this template serves.
 * @type EE_Promotion_Scope $scope  The EE_Promotion_Scope object for labels etc.
 * @type string $header_content	Any header content for this template and scope.
 * @type string $filters 		Any filters this selector might use
 * @type bool   $show_filters   Whether to show the filters container by default.
 * @type string $items_to_select	All the selectable items for this page view.
 * @type string $items_paging		This  will be the html string for the paging of items.
 * @type array $selected_items 	Array of IDs for the selected items
 *       					that this promotion applies to.
 * @type string $display_selected_label The test for the display selected label trigger/toggle.
 */
$show_filters = ! $show_filters ? ' style="display:none"' : '';
?>
<div class="ee-promotions-applies-to-main-container" id="ee-promotions-applies-to-<?php echo $scope_slug; ?>">
	<div class="ee-promotions-selected-count-container">
		<p>
			<?php
			if ( $number_of_selected_items < 2 ) {
				// singular : This promotion is currently only applied to 1 event
				echo sprintf(
					__( 'This promotion is currently only applied to :%3$s %1$s %2$s %3$s', 'event_espresso' ),
					'<span class="ee-promotions-selected-count promotion-count-bubble">' . $number_of_selected_items . '</span>',
					strtolower($scope->label->singular),
					'<br />'
				);
			} else {
				// plural : This promotion is currently applied to 5 events
				echo sprintf(
					__( 'This promotion is currently applied to :%3$s %1$s %2$s %3$s', 'event_espresso' ),
					'<span class="ee-promotions-selected-count promotion-count-bubble">' . $number_of_selected_items . '</span>',
					strtolower($scope->label->plural),
					'<br />'
				);
			}
			?>
		</p>
	</div>
	<?php if ( !empty($filters) ) : ?>

		<div class="ee-promotions-applies-to-filters">
			<p><?php _e('Advanced Filters', 'event_espresso'); ?><span class="dashicons dashicons-admin-settings ee-toggle-filters" data-filter-container=".ee-promotions-filter-settings"></span></p>
			<div class="ee-promotions-filter-settings"<?php echo $show_filters; ?>>
				<p><?php printf( __('Change these settings to filter the %s displayed in the box below.', 'event_espresso'), strtolower($scope->label->plural) ); ?></p>
				<?php echo $filters; ?>
				<button class="button secondary-button right" id="ee-apply-promotion-filter"><?php _e('Apply', 'event_espresso'); ?></button><span class="spinner"></span>
			</div>
			<div style="clear:both;"></div>
		</div>
	<?php endif; ?>
	<div class="ee-promotions-applies-to-selector">
		<?php echo $header_content; ?>
		<input class="ee-select-all-trigger" type="checkbox" id="ee-select-all-<?php echo $scope_slug; ?>"><label class="ee-select-all-label" for="ee-select-all-<?php echo $scope_slug; ?>"><?php _e('select all below', 'event_espresso'); ?></label>
		<div class="ee-sort-container right">
			<span class="ee-sort-text ee-sort-trigger clickable"><?php _e('sort', 'event_espresso'); ?></span><span class="dashicons dashicons-arrow-down ee-sort-trigger clickable"><span>
			<span style="display:none" id="ee-promotion-items-sort-order">DESC</span>
		</div>
		<div style="clear:both"></div>
		<!-- box for containing dynamically retrieved items to select -->
		<div class="ee-promotions-applies-to-items-container">
			<?php echo $items_to_select; ?>
		</div>
		<div class="ee-promotions-applies-to-paging">
			<?php echo $items_paging; ?>
		</div>
		<div style="clear:both"></div>
		<input type="hidden" id="ee-selected-items-<?php echo $scope_slug; ?>" name="ee_promotions_applied_selected_items_<?php echo $scope_slug; ?>" value="<?php echo implode(',',$selected_items); ?>">
		<input value="1" type="checkbox" class="ee-display-selected-only-trigger" id="ee-display-selected-trigger-<?php echo $scope_slug; ?>"><label class="ee-display-selected-trigger-label" for="ee-display-selected-trigger-<?php echo $scope_slug; ?>"><?php echo $display_selected_label; ?></label><br />
	</div>
	<?php echo $footer_content; ?>
	<p class="small-text important-notice"><?php _e( 'Please note that any scope items in the box above that have had promotions redeemed, will have their checkboxes greyed out and can not be deselected.  Any scope items that have not had any promotions redeemed can be deselected. This is done to maintain accounting accuracy.', 'event_espresso'); ?></p>
</div>
