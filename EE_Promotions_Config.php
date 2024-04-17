<?php

use EventEspresso\core\services\loaders\LoaderFactory;

/**
 * EE_Promotions_Config
 * Class defining the Promotions Config object stored on EE_Registry::instance->CFG
 *
 * @since      1.0.0
 * @package    EE4 Promotions
 * @subpackage config
 * @author     Darren Ethier
 */
class EE_Promotions_Config extends EE_Config_Base
{
    /**
     * Holds all the EE_Promotion_Scope objects that are registered for promotions.
     *
     * @since 1.0.0
     * @type EE_Promotion_Scope[]|null $scopes
     */
    public ?array $scopes = [];

    /**
     * what to call promo codes on the frontend. ie: Promo codes, coupon codes, etc
     *
     * @since 1.0.0
     * @type stdClass|null $label
     */
    public ?stdClass $label = null;

    public ?string $banner_template = 'promo-banner-ribbon.template.php';

    public ?string $ribbon_banner_color = 'lite-blue';

    protected ?bool $_affects_tax = false;


    public function __construct()
    {
        $this->label           = new stdClass();
        $this->label->singular = esc_html__('Promotion Code', 'event_espresso');
        $this->label->plural   = esc_html__('Promotion Codes', 'event_espresso');
        add_action('AHEE__EE_Config___load_core_config__end', [$this, 'init'], 99);
    }


    /**
     *    init
     *
     * @return void
     */
    public function init()
    {
        static $initialized = false;
        if ($initialized) {
            return;
        }
        $initialized           = true;
        $this->scopes          = $this->_get_scopes();
        $this->label           = new stdClass();
        $this->label->singular = apply_filters(
            'FHEE__EE_Promotions_Config____construct__label_singular',
            esc_html__('Promotion Code', 'event_espresso')
        );
        $this->label->plural   = apply_filters(
            'FHEE__EE_Promotions_Config____construct__label_plural',
            esc_html__('Promotion Codes', 'event_espresso')
        );
    }


    /**
     * @return array
     */
    private function _get_scopes(): array
    {
        static $scopes = [];
        $scopes_to_register = apply_filters(
            'FHEE__EE_Promotions_Config___get_scopes__scopes_to_register',
            glob(EE_PROMOTIONS_PATH . 'lib/scopes/*.lib.php')
        );
        foreach ($scopes_to_register as $scope) {
            $class_name = EEH_File::get_classname_from_filepath_with_standard_filename($scope);
            // if parent let's skip - it's already been required.
            if ($class_name == 'EE_Promotion_Scope') {
                continue;
            }
            $loaded = require_once($scope);
            // avoid instantiating classes twice by checking whether file has already been loaded
            // ( first load returns (int)1, subsequent loads return (bool)true )
            if ($loaded === 1) {
                if (class_exists($class_name)) {
                    $promotion_scope = LoaderFactory::getShared($class_name);
                    if (! $promotion_scope instanceof EE_Promotion_Scope) {
                        throw new DomainException(
                            sprintf(
                                esc_html__('Invalid or missing promotion_scope class: %1$s', 'event_espresso'),
                                $class_name
                            )
                        );
                    }
                    $scopes[ $promotion_scope->slug ] = $promotion_scope;
                }
            }
        }
        return $scopes;
    }


    public function __sleep(): array
    {
        // remove 'scopes' from array of class properties via array_filter()
        return array_filter(
            array_keys((array) $this),
            function ($prop) {
                return $prop !== 'scopes';
            }
        );
    }


    public function __wakeup()
    {
        $this->scopes = $this->_get_scopes();
    }


    /**
     * @return bool
     */
    public function affects_tax(): bool
    {
        return apply_filters(
            'FHEE__EE_Promotions_Config__affects_tax',
            $this->_affects_tax
        );
    }


    /**
     * @param bool|int|string|null $affects_tax
     */
    public function set_affects_tax($affects_tax)
    {
        $this->_affects_tax = filter_var($affects_tax, FILTER_VALIDATE_BOOLEAN);
    }
}
