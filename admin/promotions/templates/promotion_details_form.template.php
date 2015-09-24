<?php
/**
 * Template file for promotion details form content
 *
 * @since 1.0.0
 *
 * @package EE4 Promotions
 * @subpackage admin
 */
/**
 * The following template variables are available in this template:
 *
 * @type EE_Promotion $promotion
 * @type string 	$price_type_selector	generated selector for the available price types.
 * @type string 	$scope_selector	generated selector for the available scopes.
 */
?>
<table class="form-table" id="promotion-details-form">
	<tr>
		<th scope="row">
			<label for="PRC_name"><?php _e('Name', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<input type="text" class="regular-text" id="PRC_name" name="PRC_name" value="<?php echo $promotion->name(); ?>">
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRO_code"><?php _e('Code', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<input type="text" class="regular-text" id="PRO_code" name="PRO_code" value="<?php echo $promotion->code(); ?>">
			<span class="button-secondary clickable" id="generate-promo-code">
				<span class="dashicons dashicons-admin-network"></span><span class="generate-code-text"><?php _e('generate code', 'event_espresso'); ?></span>
			</span>
			<p class="description">
			<?php _e('clicking "generate code" uses above text field as a prefix', 'event_espresso'); ?>
				<a id="reset-promo-code-prefix" class="smaller-text" href="#"><?php _e('reset prefix', 'event_espresso'); ?></a>
			</p>
			<input type="hidden" id="PRO_code_prefix" name="PRO_code_prefix" value="">
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRT_ID"><?php _e('Type', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<?php echo $price_type_selector; ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRC_amount"><?php _e('Amount', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<input type="text" class="regular-text ee-numeric" id="PRC_amount" name="PRC_amount" value="<?php echo $promotion->amount(); ?>">
			<input type="hidden" name="PRC_ID" value="<?php echo $promotion->price_ID(); ?>">
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRO_scope"><?php _e('Scope (applied to)', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<?php echo $scope_selector; ?>
			<p class="description"><?php _e('This determines what type of items the promotion can be applied to (see sidebar to select items)', 'event_espresso'); ?></p>
			<?php if ( $promotion->redeemed() > 0 ) : ?>
				<p class="description"><span class="important-notice"><?php _e( 'Please note that this promotion\'s Scope selector has been disabled because it has been redeemed at least once. This is done to maintain accounting accuracy.', 'event_espresso'); ?></span></p>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRO_uses"><?php _e('Apply Promo to ALL Scope Items', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<?php echo $promotion_global; ?>
			<p class="description"><?php _e('If set to "Yes" then this promotion will be applied to ALL items of the Scope type selected above, without having to manually select the individual items via the "Promotion applies to..." metabox in the sidebar.', 'event_espresso'); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRO_uses"><?php _e('Promo Is Exclusive', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<?php echo $promotion_exclusive; ?>
			<p class="description"><?php _e('If set to "Yes" then this promotion can not be combined with any other promotions', 'event_espresso'); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRO_uses"><?php _e('Number of Uses', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<input type="text" class="regular-text ee-numeric" id="PRO_uses" name="PRO_uses" value="<?php echo $promotion_uses; ?>">
			<p class="description"><?php _e('per scope item (see above) - leave blank for no limit', 'event_espresso'); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRO_start"><?php _e('Valid From', 'event_espresso'); ?></label>
		</th>
		<td class="field-column ee-date-column">
			<input type="text" data-context="start" data-container="main" data-next-field="#PRO_end" class="regular-text ee-datepicker" id="PRO_start" name="PRO_start" value="<?php echo $promotion->start('Y-m-d', 'h:i a'); ?>"><span class="dashicons dashicons-calendar"></span><span class="dashicons dashicons-editor-removeformatting clickable clear-dtt" data-field="#PRO_start"></span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRO_end"><?php _e('Valid Until', 'event_espresso'); ?></label>
		</th>
		<td class="field-column ee-date-column">
			<input type="text" data-context="end" data-container="main" data-next-field="#PRO_uses" class="regular-text ee-datepicker" id="PRO_end" name="PRO_end" value="<?php echo $promotion->end('Y-m-d', 'h:i a'); ?>">
			<span class="dashicons dashicons-calendar"></span><span class="dashicons dashicons-editor-removeformatting clickable clear-dtt" data-field="#PRO_end"></span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRC_desc"><?php _e('Banner Text / Description', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<textarea class="ee-full-textarea-inp" id="PRC_desc" name="PRC_desc"><?php echo $promotion->description(); ?></textarea>
			<p class="description"><?php _e('This is the text that will be displayed in the Promotion Banners if they are being used (see Settings Tab) as well as anywhere that the Promotion details are listed.', 'event_espresso'); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRO_accept_msg"><?php _e('Accepted Message', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<textarea class="ee-full-textarea-inp" id="PRO_accept_msg" name="PRO_accept_msg"><?php echo $promotion->accept_message(); ?></textarea>
			<p class="description"><?php _e('If using Promotion Codes, this will be shown when a code has been successfully verified and applied to a registrant\'s order.', 'event_espresso'); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="PRO_decline_msg"><?php _e('Declined Message', 'event_espresso'); ?></label>
		</th>
		<td class="field-column">
			<textarea class="ee-full-textarea-inp" id="PRO_decline_msg" name="PRO_decline_msg"><?php echo $promotion->decline_message(); ?></textarea>
			<p class="description"><?php _e('If using Promotion Codes, this will be shown when a code entered by a registrant can not be verified or applied to their order.', 'event_espresso'); ?></p>
		</td>
	</tr>
</table>
