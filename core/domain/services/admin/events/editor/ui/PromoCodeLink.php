<?php

namespace EventEspresso\Promotions\core\domain\services\admin\events\editor\ui;

use EE_Error;
use EE_Event;
use EE_Promotion;
use EEM_Event;
use EEM_Promotion;
use EventEspresso\core\domain\services\admin\events\editor\ui\PermalinkHtmlHook;
use EventEspresso\core\services\admin\AdminPageModalContainer;
use ReflectionException;

class PromoCodeLink extends PermalinkHtmlHook
{
    public const REQUEST_PARAM = 'promo_code';


    /**
     * @throws ReflectionException
     * @throws EE_Error
     */
    public static function addButton(string $html, int $post_id): string
    {
        add_action('admin_print_footer_scripts', [PromoCodeLink::class, 'printJs'], 1000);
        $event = EEM_Event::instance()->get_one_by_ID($post_id);
        if (! $event instanceof EE_Event) {
            return $html;
        }
        $event_promo_codes = EEM_Promotion::instance()->getAllActiveCodePromotionsForEvent($event);
        if (empty($event_promo_codes)) {
            return $html;
        }
        $event_permalink = $event->get_permalink();

        $modal_header = esc_attr__('Event Promo Code Links', 'event_espresso');
        $instructions = esc_html__(
            'Click on the "copy to clipboard" button below the promo code you wish to share.',
            'event_espresso'
        );
        $promo_code_links = '';
        foreach ($event_promo_codes as $event_promo_code) {
            if (! $event_promo_code instanceof EE_Promotion) {
                continue;
            }
            $promo_code_links .= PromoCodeLink::formatPromoCodeLink($event_permalink, $event_promo_code);
        }
        $clipboard_error = esc_html__(
            'Clipboard access requires a secure connection (https). If not using https, then please copy the text manually.',
            'event_espresso'
        );


        $modal_html = "
        <h4 class='promoCodeLink__instructions'>$instructions</h4>
        <p id='ee-clipboard-error'
           class='promoCodeLink__clipboard-error ee-status-outline ee-status-bg--attention hidden'
        >$clipboard_error</p>
        <div class='promoCodeLink__list ee-layout-stack  ee-aria-tooltip__bounding-box'>$promo_code_links</div>
        ";

        $modal_footer = '
        <ul class="promoCodeLink__footer">
            <li class="promoCodeLink__footer-item">
                <span class="dashicons dashicons-tag ee-status-color--PRU"></span>
                <span>' . esc_html__('upcoming promotion', 'event_espresso') . '</span>
            </li>
            <li class="promoCodeLink__footer-item">
                <span class="dashicons dashicons-tag ee-status-color--PRA"></span>
                <span>' . esc_html__('currently active promotion', 'event_espresso') . '</span>
            </li>
            <li class="promoCodeLink__footer-item">
                <span class="dashicons dashicons-tag ee-status-color--PRX"></span>
                <span>' . esc_html__('expired promotion', 'event_espresso') . '</span>
            </li>
        </ul>
        ';
        new AdminPageModalContainer(
            "event-promo-code-link-$post_id",
            "event-promo-code-link-trigger-$post_id",
            $modal_header,
            $modal_html,
            $modal_footer,
            'promoCodeLink__modal'
        );
        $aria_label = esc_attr__(
            'Click to view available Event Promo Code Links that you can use to share discounts with others.',
            'event_espresso'
        );
        $html  .= "
            <a aria-label='$aria_label'
               class='button button--tiny button--secondary ee-aria-tooltip'
               id='event-promo-code-link-trigger-$post_id'
               href='#'
            >
                <span class='dashicons dashicons-tag'></span>
                $modal_header
            </a>";
        return $html;
    }


    /**
     * @throws ReflectionException
     * @throws EE_Error
     */
    private static function formatPromoCodeLink(string $event_permalink, EE_Promotion $promotion): string
    {
        $promo_code_text_id = "promo_code_text_{$promotion->ID()}";
        $promo_code_link = add_query_arg(
            [ PromoCodeLink::REQUEST_PARAM => $promotion->code() ],
            $event_permalink
        );

        $description = sprintf(
            esc_html__('%1$s%2$s%3$s', 'event_espresso'),
            '<span class="promoCodeLink__description">',
            $promotion->description(),
            '</span>'
        );
        $discount = sprintf(
            esc_html__('%1$sdiscount: %2$s%3$s', 'event_espresso'),
            '<span class="promoCodeLink__discount">',
            $promotion->pretty_amount(),
            '</span>'
        );

        $applies_to = sprintf(
            esc_html__('%1$sapplies to: %2$s%3$s', 'event_espresso'),
            '<div class="promoCodeLink__applies_to">',
            $promotion->applied_to_name('admin'),
            '</div>'
        );

        $start_date = $promotion->start('M d/y', ' ');
        $starts = $start_date
            ? sprintf(
                esc_html__('%1$sstarts: %2$s%3$s', 'event_espresso'),
                '<div class="promoCodeLink__starts">',
                $start_date,
                '</div>'
            )
            : '';

        $end_date = $promotion->end('M d/y', ' ');
        $ends = $end_date
            ? sprintf(
                esc_html__('%1$sends: %2$s%3$s', 'event_espresso'),
                '<div class="promoCodeLink__ends">',
                "&nbsp;$end_date",
                '</div>'
            )
            : '';

        $copied = esc_attr__('Copied!', 'event_espresso');
        $button_text = esc_attr__('Copy to Clipboard', 'event_espresso');
        $clipboard_icon = '
        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 16 16" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
            <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"></path>
            <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"></path>
        </svg>';

        return "
        <div class='promo_code_link_{$promotion->ID()} promoCodeLink__promo-code'>
            <span class='dashicons dashicons-tag ee-status-color--{$promotion->status()}'></span>

            <div class='promoCodeLink__details ee-layout-stack'>
                <span class='promoCodeLink__code ee-status-color--{$promotion->status()}'>{$promotion->code()}</span>
                $discount
                $description
            </div>

            <div class='promoCodeLink__extra ee-layout-stack ee-status-color--light-grey'>
                $applies_to
                $starts
                $ends
            </div>
        </div>

        <div class='promoCodeLink__input-row ee-layout-row'>
            <input type='text'
                   class='promoCodeLink__input'
                   id='$promo_code_text_id'
                   value='$promo_code_link'
                   readonly
            />
            <button aria-label='$button_text'
                    class='promoCodeLink__button button button--secondary button--icon-only ee-aria-tooltip'
                    onclick='copyPromoCodeToClipboard(\"$promo_code_text_id\", \"$copied\")'
            >
                <span class='svg-icon'>$clipboard_icon</span>
            </button>
        </div>
        ";
    }


    /**
     * does what it's named
     *
     * @return void
     */
    public static function printJs()
    {
        ?>
        <script type="text/javascript">
            /**
             * copied from https://www.w3schools.com/howto/howto_js_copy_clipboard.asp LOLZ w3schools!
             *
             * @param {string} inputID
             * @param {string} copiedText
             */
            function copyPromoCodeToClipboard(inputID, copiedText) {
                const copyText = document.getElementById(inputID);
                if (! copyText instanceof HTMLInputElement) {
                    return;
                }
                // Select the text field
                copyText.select();
                copyText.setSelectionRange(0, 99999); // For mobile devices
                // Copy the text inside the text field
                navigator.clipboard
                    .writeText(copyText.value)
                    .then(
                        /* clipboard successfully set */
                        (clipText) => {
                            const tooltip = document.getElementById(inputID);
                            tooltip.innerHTML = copiedText + clipText;
                        },
                        /* clipboard write failed */
                        () => {
                            document.getElementById('ee-clipboard-error').classList.remove('hidden');
                        },
                    );
            }
        </script>
        <?php
    }
}
