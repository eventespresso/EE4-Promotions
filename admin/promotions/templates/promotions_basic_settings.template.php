<?php
/* @var $config EE_Promotions_Config */
?>
<div class="padding">
	<h4>
		<?php _e('Promotions Settings', 'event_espresso'); ?>
	</h4>
	<table class="form-table">
		<tbody>

			<tr>
				<th><?php _e( 'Promotion Banners', 'event_espresso');?></th>
				<td>
					<?php echo EEH_Form_Fields::select( __( 'Event List Promotions Banner', 'event_espresso'), $config->banner_template, $banner_template, 'promotions[banner_template]', 'banner_template' ); ?><br/>
					<p class="description">
						<?php _e('How Non-Code Promotions* are advertised and displayed above the Ticket Selector.', 'event_espresso'); ?>
						<br />
						<span class="smaller-text">
							<?php _e('* "Non-Code Promotions" are promotions that do not use a text code and are applied automatically when all of the promotions qualifying requirements are met (ie: start date, selected events, etc).', 'event_espresso'); ?>
						</span>
					</p>
				</td>
			</tr>

			<tr>
				<th><?php _e( 'Ribbon Banner Color', 'event_espresso');?></th>
				<td>
					<?php echo EEH_Form_Fields::select( __( 'Ribbon Banner Color', 'event_espresso'), $config->ribbon_banner_color, $ribbon_banner_color, 'promotions[ribbon_banner_color]', 'ribbon_banner_color' ); ?><br/>
					<p class="description">
						<?php _e('If "Ribbon Banner" is selected above, then this determines the color of the ribbon banner.', 'event_espresso'); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th><?php _e("Reset Promotions Settings?", 'event_espresso');?></th>
				<td>
					<?php echo EEH_Form_Fields::select( __('Reset Promotions Settings?', 'event_espresso'), 0, $yes_no_values, 'reset_promotions', 'reset_promotions' ); ?><br/>
					<p class="description">
						<?php _e('Set to \'Yes\' and then click \'Save\' to confirm reset all basic and advanced Event Espresso Promotions settings to their plugin defaults.', 'event_espresso'); ?>
					</p>
				</td>
			</tr>

		</tbody>
	</table>

</div>

<input type='hidden' name="return_action" value="<?php echo $return_action?>">

