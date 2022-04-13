<?php


/**
 * Class PromotionsDatepicker
 *
 * @author  Brent Christensen
 * @since   $VID:$
 */
class PromotionsDatepicker
{
    /**
     * @var string
     */
    private $date_string;

    /**
     * id of the parent container of section where datepicker input resides
     *
     * @var string
     */
    private $container;

    /**
     * whether for a start or end date
     *
     * @var string
     */
    private $context;

    /**
     * used for the HTML input name and id
     *
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $reset_label;

    /**
     * @var string
     */
    private $trigger_label;


    /**
     * @param string $date_string
     * @param string $context
     * @param string $container
     * @param string $id
     * @param string $trigger_label
     */
    public function __construct(
        string $date_string,
        string $context,
        string $container,
        string $id,
        string $trigger_label
    ) {
        $this->date_string   = $date_string;
        $this->context       = $context;
        $this->container     = $container;
        $this->id            = $id;
        $this->trigger_label = $trigger_label;
        $this->reset_label   = esc_html__('reset date input', 'event_espresso');
    }


    public function getHtml(): string
    {
        return "
            <div class='ee-input-sidebar__wrapper'>
                <div class='ee-input-sidebar ee-input-sidebar--after'>
                    <input type='text'
                           data-context='$this->context'
                           data-container='$this->container'
                           class='ee-input-width--reg ee-datepicker'
                           id='$this->id'
                           name='$this->id'
                           value='$this->date_string'
                    >
                    <button class='button button--secondary button--icon-only ee-aria-tooltip ee-toggle-datepicker'
                            aria-label='$this->trigger_label'
                            data-target='#$this->id'
                            onclick='return false'
                            tabindex='-1'
                    >
                        <span class='dashicons dashicons-calendar' ></span >
                    </button >
                </div >
                <button class='button button--secondary button--icon-only ee-aria-tooltip clear-dtt'
                        aria-label='$this->reset_label'
                        data-field='#$this->id'
                >
                    <span class='dashicons dashicons-editor-removeformatting' ></span >
                </button >
            </div >";
    }
}