<?php

/**
 * This file is the template for the promotions applies to metabox wrapper.
 *
 * @since      1.0.0
 * @package    EE4 Promotions
 * @subpackage admin
 */

/**
 * Template variables in use for this template
 *
 * @var string             $scope_slug             The slug for the scope this template serves.
 * @var EE_Promotion_Scope $scope                  The EE_Promotion_Scope object for labels etc.
 * @var string             $header_content         Any header content for this template and scope.
 * @var string             $filters                Any filters this selector might use
 * @var string             $items_to_select        All the selectable items for this page view.
 * @var string             $items_paging           This  will be the html string for the paging of items.
 * @var array              $selected_items         Array of IDs for the selected items
 *                          that this promotion applies to.
 * @var string             $display_selected_label The test for the display selected label trigger/toggle.
 * @var int                $number_of_selected_items
 * @var string             $footer_content
 */
?>
<div class="ee-promotions-applies-to-main-container" id="ee-promotions-applies-to-<?php echo $scope_slug; ?>">
    <div class="ee-promotions-selected-count-container">
        <p>
            <?php
            if ($number_of_selected_items < 2) {
                // singular : This promotion is currently only applied to 1 event
                echo sprintf(
                    esc_html__('This promotion is currently only applied to :%3$s %1$s %2$s %3$s', 'event_espresso'),
                    '<span class="ee-promotions-selected-count promotion-count-bubble">'
                    . $number_of_selected_items
                    . '</span>',
                    '<span class="ee-promotions-selected-event">' . strtolower($scope->label->plural) . '</span>',
                    '<br />'
                );
            } else {
                // plural : This promotion is currently applied to 5 events
                echo sprintf(
                    esc_html__('This promotion is currently applied to :%3$s %1$s %2$s %3$s', 'event_espresso'),
                    '<span class="ee-promotions-selected-count promotion-count-bubble">'
                    . $number_of_selected_items
                    . '</span>',
                    '<span class="ee-promotions-selected-event">' . strtolower($scope->label->plural) . '</span>',
                    '<br />'
                );
            }
            ?>
        </p>
    </div>
    <?php if (! empty($filters)) : ?>
        <?php echo $filters; ?>
    <?php endif; ?>
    <div class="ee-promotions-applies-to-selector">
        <div class='ee-display-selected'>
            <?php echo $header_content; ?>
            <label class="ee-select-all-label" for="ee-select-all-<?php echo $scope_slug; ?>">
                <input class="ee-select-all-trigger ee-input-size--small" type="checkbox" id="ee-select-all-<?php echo $scope_slug; ?>">
                <?php esc_html_e('select all below', 'event_espresso'); ?>
            </label>
            <label class='ee-display-selected-trigger-label'
                   for="ee-display-selected-trigger-<?php echo $scope_slug; ?>"
            >
                <input value='1'
                       type='checkbox'
                       class='ee-display-selected-only-trigger ee-input-size--small'
                       id="ee-display-selected-trigger-<?php echo $scope_slug; ?>"
                >
                <?php echo $display_selected_label; ?>
            </label>
            <input type='hidden'
                   id="ee-selected-items-<?php echo $scope_slug; ?>"
                   name="ee_promotions_applied_selected_items_<?php echo $scope_slug; ?>"
                   value="<?php echo implode(',', $selected_items); ?>"
            >
        </div>
        <div class="ee-sort-container">
            <h4><?php esc_html_e('Events', 'event_espresso'); ?></h4>
            <span>
                <span class="ee-sort-text ee-sort-trigger clickable">
                    <?php esc_html_e('sort', 'event_espresso'); ?>
                </span>
                <span class="dashicons dashicons-arrow-down ee-sort-trigger clickable"><span>
                <span style="display:none" id="ee-promotion-items-sort-order">DESC</span>
            </span>
        </div>
        <div style="clear:both"></div>
        <!-- box for containing dynamically retrieved items to select -->
        <div class="ee-promotions-applies-to-items-container">
            <?php echo $items_to_select; ?>
        </div>
        <div class="ee-promotions-applies-to-paging">
            <?php echo $items_paging; ?>
        </div>
    </div>
    <?php echo $footer_content; ?>
    <p class="small-text ee-attention">
        <?php esc_html_e(
            'Please note that any scope items in the box above that have had promotions redeemed, will have their checkboxes greyed out and can not be deselected.  Any scope items that have not had any promotions redeemed can be deselected. This is done to maintain accounting accuracy.',
            'event_espresso'
        ); ?>
    </p>
</div>
