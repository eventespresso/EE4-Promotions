<?php
if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
/** @type EE_Promotion $promotion */
?>

	<div id="ee-upcoming-promotions-container-dv-<?php echo $PRO_ID; ?>" class="ee-upcoming-promotions-container-dv">
	<div class="ee-promo-upcoming-promotions-dates-dv smaller-text"><?php echo $promo_dates; ?></div>
		<div class="ee-promo-upcoming-promotions-main-dv <?php echo $promo_bg_color; ?>">
			<h5 class="ee-upcoming-promotions-h5"><?php echo $promo_header; ?></h5>
			<?php if ( $promo_desc ) : ?>
			<p class="ee-promo-upcoming-promotions-main-text-pg">
				<?php echo $promo_desc; ?>
			</p>
	<?php endif; ?>
			<p class="ee-promo-upcoming-promotions-additional-details-pg">
				<span class="smaller-text"><?php _e( 'Discount Amount: ', 'event_espresso' ); ?></span><?php echo $promo_amount; ?><br />
				<span class="smaller-text"><?php printf( __( 'Applies to the following %1$s:', 'event_espresso' ), $promo_scope ); ?></span><br />
			</p>
			<ul class="ee-promo-upcoming-promotions-applies-to-ul small-text">
				<?php foreach ( $promo_applies as $applies_to_name ) : ?>
					<li class="ee-promo-upcoming-promotions-applies-to-li"><?php echo $applies_to_name;?></li>
				<?php endforeach;?>
			</ul>
		</div>
	</div>

<?php
// End of file promo-ribbon-banner-lite-blue.template.php
// Location: /promo-ribbon-banner-lite-blue.template.php