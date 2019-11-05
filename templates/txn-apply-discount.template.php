<?php if (! defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}
?>
<li>
    <input type="text" placeholder="<?php esc_attr_e('Discount Code', 'event_espresso'); ?>e">
    <a id="display-txn-admin-apply-discount-code" class="button-secondary no-icon no-hide"
        rel="txn-admin-apply-payment"> <!--display-the-hidden -->
        <?php esc_html_e('Apply Discount Code', 'event_espresso'); ?>
    </a>
</li>
