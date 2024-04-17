<?php

/**
 * @type EE_Promotion $promotion
 * @type int $PRO_ID
 * @type string $promo_dates
 * @type string $promo_bg_color
 * @type string $promo_header
 * @type string $promo_desc
 * @type string $promo_amount
 * @type array $promo_scopes
 * @type bool $promo_is_global
 */


?>

    <div id="ee-upcoming-promotions-container-dv-<?php echo $PRO_ID; ?>" class="ee-upcoming-promotions-container-dv">
    <div class="ee-promo-upcoming-promotions-dates-dv smaller-text"><?php echo $promo_dates; ?></div>
        <div class="ee-promo-upcoming-promotions-main-dv <?php echo $promo_bg_color; ?>">
            <h5 class="ee-upcoming-promotions-h5"><?php echo $promo_header; ?></h5>
            <?php if ($promo_desc) : ?>
            <p class="ee-promo-upcoming-promotions-main-text-pg">
                <?php echo $promo_desc; ?>
            </p>
            <?php endif; ?>
            <p class="ee-promo-upcoming-promotions-additional-details-pg">
                <span class="smaller-text"><?php esc_html_e('Discount Amount: ', 'event_espresso'); ?></span><?php echo $promo_amount; ?><br />
            </p>
            <?php foreach ($promo_scopes as $promo_scope => $objects) : ?>
                <?php if ($promo_is_global) : ?>
                    <b><?php echo $objects;?></b>
                <?php else : ?>
                    <b class="smaller-text"><?php printf(__('Applies to the following %1$s(s):', 'event_espresso'), $promo_scope); ?></b><br />
                    <ul class="ee-promo-upcoming-promotions-applies-to-ul small-text">
                        <?php foreach ($objects as $applies_to_name) : ?>
                            <li class="ee-promo-upcoming-promotions-applies-to-li"><?php echo $applies_to_name;?></li>
                        <?php endforeach;?>
                    </ul>
                <?php endif;?>
            <?php endforeach;?>
        </div>
    </div>
