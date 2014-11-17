<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
/** @type int $EVT_ID */
/** @type string $ribbon_color */
/** @type string $banner_text */
?>

	<div id="ee-promo-banner-container-dv-<?php echo $EVT_ID; ?>" class="ee-promo-banner-container-dv ee-promo-banner-ribbon-dv ee-promo-banner-ribbon-<?php echo $ribbon_color; ?>-dv">

		<div class="ee-promo-banner-ribbon-back-dv ee-promo-banner-ribbon-back-left-dv">
			<div class="ee-promo-banner-ribbon-arrow-dv ee-promo-banner-ribbon-arrow-top-dv"></div>
			<div class="ee-promo-banner-ribbon-arrow-dv ee-promo-banner-ribbon-arrow-bottom-dv"></div>
		</div>

		<div class="ee-promo-banner-ribbon-skew-dv ee-promo-banner-ribbon-skew-left-dv"></div>

		<div class="ee-promo-banner-ribbon-main-dv">
			<div class="ee-promo-banner-text-dv"><?php echo $banner_text; ?></div>
		</div>

		<div class="ee-promo-banner-ribbon-skew-dv ee-promo-banner-ribbon-skew-right-dv"></div>

		<div class="ee-promo-banner-ribbon-back-dv ee-promo-banner-ribbon-back-right-dv">
			<div class="ee-promo-banner-ribbon-arrow-dv ee-promo-banner-ribbon-arrow-top-dv"></div>
			<div class="ee-promo-banner-ribbon-arrow-dv ee-promo-banner-ribbon-arrow-bottom-dv"></div>
		</div>

	</div>

<?php
// End of file promo-ribbon-banner.template.php
// Location: wp-content/plugins/espresso-promotions/templates/promo-banner-ribbon.template.php