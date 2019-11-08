<?php if (! defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}
?>
<li>
    <input type="text" id="ee-promotion-code-input" placeholder="<?php esc_attr_e('Discount Code', 'event_espresso'); ?>">
    <a id="ee-promotion-code-submit" class="button-secondary no-icon no-hide"
        rel="txn-admin-apply-payment"> <!--display-the-hidden -->
        <?php esc_html_e('Apply Discount Code', 'event_espresso'); ?>
    </a>
</li>
