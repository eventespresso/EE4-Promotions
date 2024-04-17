<?php

/*
 * EEW_Promotions
 * Displays a List of Promotions in the Sidebar
 *
 * @package     Event Espresso
 * @subpackage 	espresso-promotions
 * @author      Brent Christensen
 * @since       4.3
 */

class EEW_Promotions extends WP_Widget
{
    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'ee-promotions-widget',
            esc_html__('Event Espresso Promotions Widget', 'event_espresso'),
            ['description' => esc_html__('Displays Espresso Promotions in a widget.', 'event_espresso')],
            [
                'width'   => 300,
                'height'  => 350,
                'id_base' => 'ee-promotions-widget',
            ]
        );
    }


    /**
     * Back-end widget form.
     *
     * @param array $instance Previously saved values from database.
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     * @see WP_Widget::form()
     */
    public function form($instance)
    {
        EE_Registry::instance()->load_helper('Form_Fields');
        EE_Registry::instance()->load_class('Question_Option', [], false, false, true);
        $instance = wp_parse_args(
            (array) $instance,
            ['title' => esc_html__('Current Promotions', 'event_espresso')]
        );
        ?>

        <p>
            <label for="<?php
            echo $this->get_field_id('title'); ?>"
            >
                <?php
                esc_html_e('Title:', 'event_espresso'); ?>
            </label>
            <input type="text" id="<?php
            echo $this->get_field_id('title'); ?>" name="<?php
            echo $this->get_field_name('title'); ?>" width="20" value="<?php
            echo $instance['title']; ?>"
            />
        </p>
        <?php
    }


    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $instance     Previously saved values from database.
     * @return array Updated safe values to be saved.
     * @see WP_Widget::update()
     */
    public function update($new_instance, $instance)
    {
        // Strip tags (if needed) and update the widget settings.
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }


    /**
     * Front-end display of widget.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     * @throws EE_Error
     * @throws ReflectionException
     * @see WP_Widget::widget()
     */
    public function widget($args, $instance)
    {
        // get the current post
        global $post;
        if (isset($post->post_content)) {
            // check the post content for the short code
            if (strpos($post->post_content, '[ESPRESSO_PROMOTIONS') === false) {
                EED_Promotions::$shortcode_active = true;
                // Before widget (defined by themes).
                echo $args['before_widget'];
                // Title of widget (before and after defined by themes).
                $title = apply_filters('widget_title', $instance['title']);
                if (! empty($title)) {
                    echo $args['before_title'] . $title . $args['after_title'];
                }
                EED_Promotions::instance()->enqueue_scripts();
                echo EED_Promotions::instance()->display_promotions([]);
                // After widget (defined by themes).
                echo $args['after_widget'];
            }
        }
    }
}
