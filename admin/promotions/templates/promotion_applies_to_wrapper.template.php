<?php
/**
 * This file is the template for the promotions applies to metabox wrapper.
 *
 * @since 1.0.0
 * @package EE4 Promotions
 * @subpackage admin
 */
/**
 * Tempate variables in use for this template
 * @type string $scope_slug		The slug for the scope this template serves.
 * @type string $header_content	Any header content for this template and scope.
 * @type string $filters 		Any filters this selector might use
 * @type string $items_to_select	All the selectable items for this page view.
 * @type string $items_paging		This  will be the html string for the paging of items.
 * @type string $selected_items 	This is a comma delimited string of IDs for the selected items
 *       					that this promotion applies to.
 * @type string $display_selected_label The test for the display selected label trigger/toggle.
 */
?>
<div id="ee-promotions-applies-to-<?php echo $scope_slug; ?>">
	<?php echo $header_content; ?>
	<?php echo $filters; ?>
	<div class="ee-promotions-applies-to-selector">
		<input type="checkbox" id="ee-select-all-<?php echo $scope_slug; ?>"><label class="ee-select-all-label" for="ee-select-all-<?php echo $scope_slug; ?>"><?php _e('select all below', 'event_espresso'); ?></label>
		<div class="ee-sort-container right">
			<span class="ee-sort-text ee-sort-trigger clickable"><?php _e('sort', 'event_espresso'); ?></span><span class="dashicons dashicons-arrow-up ee-sort-trigger clickable"><span>
		</div>
		<div style="clear:both"></div>
		<!-- box for containing dynamically retrieved items to select -->
		<div class="ee-promotions-applies-to-items-container">
			<?php echo $items_to_select; ?>
		</div>
		<div class="ee-promotions-applies-to-paging">
			<?php echo $items_paging; ?>
		</div>
		<input type="hidden" id="ee-selected-items-<?php echo $scope_slug; ?>" name="ee_promotions_applied_selected_items_<?php echo $scope_slug; ?>" value="<?php echo $selected_items; ?>">
		<input type="checkbox" id="ee-display-selected-trigger-<?php echo $scope_slug; ?>"><label class="ee-display-selected-trigger-label" for="ee-display-selected-trigger-<?php echo $scope_slug; ?>"><?php echo $display_selected_label; ?></label>
	</div>
	<?php echo $footer_content; ?>
</div>
