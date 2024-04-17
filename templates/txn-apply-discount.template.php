<?php ?>
<span class="ee-layout-row ee-layout-row--inline" style="width: var(--ee-admin-sidebar-width);">
    <label for="ee-promotion-code-input" class="screen-reader-text"><?php esc_html__('Discount Code', 'event_espresso'); ?></label>
    <input type="text" id="ee-promotion-code-input" placeholder="<?php esc_attr_e('Discount Code', 'event_espresso'); ?>">
    <a id="ee-promotion-code-submit" class="button button--secondary no-icon no-hide"
        rel="txn-admin-apply-payment"> <!--display-the-hidden -->
        <?php esc_html_e('Apply Discount Code', 'event_espresso'); ?>
    </a>
</span>
