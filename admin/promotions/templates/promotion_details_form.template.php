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
		<td class="label-column">
			<label for="PRC_name"><?php _e('Name', 'event_espresso'); ?></label>
		</td>
		<td class="field-column">
			<input class="regular-text" id="PRC_name" name="PRC_name" value="<?php echo $promotion->name(); ?>">
		</td>
	</tr>
	<tr>
		<td class="label-column">
			<label for="PRO_code"><?php _e('Code', 'event_espresso'); ?></label>
		</td>
		<td class="field-column">
			<input class="regular-text" id="PRO_code" name="PRO_code" value="<?php echo $promotion->code(); ?>"><span class="description"><?php _e('Optional', 'event_espresso'); ?></span><br />
			<div class="clickable" id="generate-promo-code"><span class="dashicons dashicons-tagcloud"></span><span class="generate-code-text"><?php _e('generate random code', 'event_espresso'); ?></span></div><span class="description"><?php _e('uses above text field as a prefix', 'event_espresso'); ?></span>
		</td>
	</tr>
	<tr>
		<td class="label-column">
			<label for="PRT_ID"><?php _e('Type', 'event_espresso'); ?></label>
		</td>
		<td class="field-column">
			<?php echo $price_type_selector; ?>
		</td>
	</tr>
	<tr>
		<td class="label-column">
			<label for="PRC_amount"><?php _e('Amount', 'event_espresso'); ?></label>
		</td>
		<td class="field-column">
			<input class="regular-text ee-numeric" id="PRC_amount" name="PRC_amount" value="<?php echo $promotion->amount(); ?>">
			<input type="hidden" name="PRC_ID" value="<?php echo $promotion->price_ID(); ?>">
		</td>
	</tr>
	<tr>
		<td class="label-column">
			<label for="PRO_scope"><?php _e('Scope (applied to)', 'event_espresso'); ?></label>
		</td>
		<td class="field-column">
			<?php echo $scope_selector; ?><span class="description"><?php _e('see sidebar to select items', 'event_espresso'); ?></span>
		</td>
	</tr>
	<tr>
		<td class="label-column">
			<label for="PRO_start"><?php _e('Valid From', 'event_espresso'); ?></label>
		</td>
		<td class="field-column ee-date-column">
			<input data-context="start" data-container="main" data-next-field="#PRO_end" class="regular-text ee-datepicker" id="PRO_start" name="PRO_start" value="<?php echo $promotion->start('Y-m-d', 'h:i a'); ?>"><span class="dashicons dashicons-calendar"></span><span class="dashicons dashicons-editor-removeformatting clickable clear-dtt" data-field="#PRO_start"></span>
		</td>
	</tr>
	<tr>
		<td class="label-column">
			<label for="PRO_end"><?php _e('Valid Until', 'event_espresso'); ?></label>
		</td>
		<td class="field-column ee-date-column">
			<input data-context="end" data-container="main" data-next-field="#PRO_uses" class="regular-text ee-datepicker" id="PRO_end" name="PRO_end" value="<?php echo $promotion->end('Y-m-d', 'h:i a'); ?>"><span class="dashicons dashicons-calendar"></span><span class="dashicons dashicons-editor-removeformatting clickable clear-dtt" data-field="#PRO_end"></span>
		</td>
	</tr>
	<tr>
		<td class="label-column">
			<label for="PRO_uses"><?php _e('Number of Uses', 'event_espresso'); ?></label>
		</td>
		<td class="field-column">
			<input class="regular-text ee-numeric" id="PRO_uses" name="PRO_uses" value="<?php echo $promotion->uses(); ?>"><span class="description"><?php _e('per scope', 'event_espresso'); ?></span>
		</td>
	</tr>
	<tr>
		<td class="label-column">
			<label for="PRC_desc"><?php _e('Description', 'event_espresso'); ?></label>
		</td>
		<td class="field-column">
			<textarea class="ee-full-textarea-inp" id="PRC_desc" name="PRC_desc"><?php echo $promotion->description(); ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="label-column">
			<label for="PRO_accept_msg"><?php _e('Accepted Message', 'event_espresso'); ?></label>
		</td>
		<td class="field-column">
			<textarea class="ee-full-textarea-inp" id="PRO_accept_msg" name="PRO_accept_msg"><?php echo $promotion->accept_message(); ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="label-column">
			<label for="PRO_decline_msg"><?php _e('Declined Message', 'event_espresso'); ?></label>
		</td>
		<td class="field-column">
			<textarea class="ee-full-textarea-inp" id="PRO_decline_msg" name="PRO_decline_msg"><?php echo $promotion->decline_message(); ?></textarea>
		</td>
	</tr>
</table>
