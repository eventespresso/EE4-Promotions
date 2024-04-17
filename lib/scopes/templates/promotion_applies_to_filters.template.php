<?php

/**
 * This file is the template for the promotions applies to filters
 *
 * @since      1.0.0
 * @package    EE4 Promotions
 * @subpackage admin
 *
 * Template variables in use for this template
 *
 * @var EE_Promotion_Scope   $scope The EE_Promotion_Scope object for labels etc.
 * @var PromotionsDatepicker $start_date_picker
 * @var PromotionsDatepicker $end_date_picker
 * @type bool                $show_filters
 * @type array               $categories
 * @type string              $default
 * @type string              $existing_name
 * @type string              $expired_checked
 */
$show_filters = ! $show_filters ? ' style="display:none"' : '';
?>
<div class='ee-promotions-applies-to-filters'>
    <div class="ee-promotions-applies-to-filters__heading-row ee-layout-row">
        <h3 class='ee-promotions-applies-to-filters__heading'>
            <?php esc_html_e('Advanced Filters', 'event_espresso'); ?>
        </h3>
        <button aria-label="<?php esc_html_e('click to view advanced filters', 'event_espresso'); ?>"
                class="button button--icon-only ee-toggle-filters ee-aria-tooltip"
                data-filter-container=".ee-promotions-filter-settings"
        >
            <span class="dashicons dashicons-admin-settings"></span>
        </button>
    </div>
    <div class="ee-promotions-filter-settings"<?php echo $show_filters; ?>>
        <p>
            <?php printf(
                esc_html__(
                    'Change these settings to filter the %s displayed in the box below.',
                    'event_espresso'
                ),
                strtolower($scope->label->plural)
            ); ?>
        </p>

        <div>
            <label for='EVT_title_filter' class='ee-promotions-filter-lbl'>
                <?php esc_html_e('event title', 'event_espresso'); ?>
            </label>
            <input type='text'
                   id='EVT_title_filter'
                   name='EVT_title_filter'
                   class='promotions-general-filter ee-text-inp'
                   value="<?php echo $existing_name; ?>"
                   placeholder="<?php esc_html_e('Event Name', 'event_espresso'); ?>"
            >
        </div>

        <div>
            <label for='EVT_CAT_ID' class='ee-promotions-filter-lbl'>
                <?php esc_html_e('event categories', 'event_espresso'); ?>
            </label>
            <?php echo EEH_Form_Fields::select_input(
                'EVT_CAT_ID',
                $categories,
                $default,
                '',
                'ee-input-width--reg',
                false
            ); ?>
        </div>

        <div>
            <label for='EVT_start_date_filter' class='ee-promotions-filter-lbl'>
                <?php esc_html_e('start date', 'event_espresso'); ?>
            </label>
            <?php echo $start_date_picker->getHtml(); ?>
        </div>

        <div>
            <label for='EVT_end_date_filter' class='ee-promotions-filter-lbl'>
                <?php esc_html_e('end date', 'event_espresso'); ?>
            </label>
            <?php echo $end_date_picker->getHtml(); ?>
        </div>

        <div>
            <label for='include-expired-events-filter' class='ee-promotions-filter-lbl single-line-filter-label ee-input--after'>
                <?php esc_html_e('Include expired events?', 'event_espresso'); ?>
                <input type="checkbox"
                       id="include-expired-events-filter"
                       name="include_expired_events_filter"
                       class="promotions-general-filter ee-checkbox-inp ee-input-size--small"
                       value="1"
                    <?php echo $expired_checked; ?>
                >
            </label>
        </div>

        <button class="button secondary-button right" id="ee-apply-promotion-filter">
            <?php esc_html_e('Apply', 'event_espresso'); ?>
        </button>
        <span class="spinner"></span>
    </div>
    <div style="clear:both;"></div>
</div>
