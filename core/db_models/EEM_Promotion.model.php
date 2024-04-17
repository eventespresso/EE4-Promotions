<?php

/**
 * Promotion Model
 *
 * @package             Event Espresso
 * @subpackage          includes/models/
 * @author              Michael Nelson
 * @method EE_Promotion|null get_one(array $query_params = [])
 * @method EE_Promotion[] get_all(array $query_params = [])
 */
class EEM_Promotion extends EEM_Soft_Delete_Base
{
    public const LINE_ITEM_OBJ_TYPE = 'Promotion';

    protected static ?EEM_Promotion $_instance = null;


    /**
     * @param string|null $timezone
     * @throws EE_Error
     * @throws Exception
     */
    protected function __construct(?string $timezone = '')
    {
        $this->singular_item = esc_html__('Promotion', 'event_espresso');
        $this->plural_item   = esc_html__('Promotions', 'event_espresso');

        $this->_tables = [
            'Promotion' => new EE_Primary_Table('esp_promotion', 'PRO_ID'),
        ];

        $this->_fields = [
            'Promotion' => [
                'PRO_ID'          => new EE_Primary_Key_Int_Field('PRO_ID', esc_html__('ID', 'event_espresso')),
                'PRC_ID'          => new EE_Foreign_Key_Int_Field(
                    'PRC_ID',
                    esc_html__("Price ID", "event_espresso"),
                    false,
                    0,
                    'Price'
                ),
                'PRO_scope'       => new EE_Plain_Text_Field(
                    'PRO_scope',
                    esc_html__("Scope", "event_espresso"),
                    false,
                    ''
                ),
                'PRO_start'       => new EE_Datetime_Field(
                    'PRO_start',
                    esc_html__("Start Date/Time", "event_espresso"),
                    true,
                    null,
                    $timezone
                ),
                'PRO_end'         => new EE_Datetime_Field(
                    'PRO_end',
                    esc_html__("End Date/Time", "event_espresso"),
                    true,
                    null,
                    $timezone
                ),
                'PRO_code'        => new EE_Plain_Text_Field(
                    'PRO_code', esc_html__("Code", "event_espresso"), true, ''
                ),
                'PRO_uses'        => new EE_Integer_Field(
                    'PRO_uses',
                    esc_html__(
                        "Times this can be used in a given scope",
                        "event_espresso"
                    ),
                    false,
                    EE_INF_IN_DB
                ),
                'PRO_global'      => new EE_Boolean_Field(
                    'PRO_global',
                    esc_html__("Applies to ALL Scope items", "event_espresso"),
                    false,
                    false
                ),
                'PRO_global_uses' => new EE_Integer_Field(
                    'PRO_global_uses',
                    esc_html__(
                        "Times it can be used in all scopes",
                        "event_espresso"
                    ),
                    false,
                    EE_INF_IN_DB
                ),
                'PRO_exclusive'   => new EE_Boolean_Field(
                    'PRO_exclusive',
                    esc_html__("Exclusive? (ie, can't be used with other promotions)", "event_espresso"),
                    false,
                    apply_filters('FHEE__EEM_Promotion__promotions_exclusive_default', true)
                ),
                'PRO_accept_msg'  => new EE_Simple_HTML_Field(
                    'PRO_accept_msg',
                    esc_html__("Acceptance Message", "event_espresso"),
                    false,
                    esc_html__("Accepted", "event_espresso")
                ),
                'PRO_decline_msg' => new EE_Simple_HTML_Field(
                    'PRO_decline_msg',
                    esc_html__("Declined Message", "event_espresso"),
                    false,
                    esc_html__("Declined", "event_espresso")
                ),
                'PRO_default'     => new EE_Boolean_Field(
                    'PRO_default',
                    esc_html__(
                        "Usable by default on all new items within promotion's scope",
                        "event_espresso"
                    ),
                    false,
                    false
                ),
                'PRO_order'       => new EE_Integer_Field(
                    'PRO_order',
                    esc_html__("Order", "event_espresso"),
                    false,
                    0
                ),
                'PRO_deleted'     => new EE_Trashed_Flag_Field(
                    'PRO_deleted',
                    esc_html__("Deleted", 'event_espresso'),
                    false,
                    false
                ),
                'PRO_wp_user'     => new EE_WP_User_Field(
                    'PRO_wp_user',
                    esc_html__('Promotion Creator', 'event_espresso'),
                    false
                ),
            ],
        ];

        $this->_model_relations = [
            'Price'            => new EE_Belongs_To_Relation(),
            'Promotion_Object' => new EE_Has_Many_Relation(),
            'Line_Item'        => new EE_Has_Many_Any_Relation(),
        ];

        parent::__construct($timezone);
    }


    /**
     * get_promotion_details_via_code
     *
     * @param string $promo_code
     * @param array  $additional_query_params
     * @return EE_Promotion|null
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function get_promotion_details_via_code(
        string $promo_code = '',
        array $additional_query_params = []
    ): ?EE_Promotion {
        return $this->get_one(
            array_replace_recursive(
                $additional_query_params,
                [
                    [
                        'PRO_code'    => $promo_code,
                        'PRO_deleted' => false,
                    ],
                ],
                // query params for calendar controlled expiration
                $this->_get_promotion_expiration_query_params()
            )
        );
    }


    /**
     * get_all_active_codeless_promotions
     * retrieves all promotions that are currently active based on the current time and do
     * NOT utilize a code
     * Note this DOES include promotions that have no dates set.
     *
     * @param array $query_params
     * @return EE_Promotion[]
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function get_all_active_codeless_promotions(array $query_params = []): array
    {
        return $this->get_all(
            array_replace_recursive(
                [
                    [
                        'PRO_code'    => null,
                        'PRO_deleted' => false,
                    ],
                ],
                // query params for calendar controlled expiration
                $this->_get_promotion_expiration_query_params(),
                // incoming $query_params array filtered to remove null values and empty strings
                array_filter($query_params, 'EEM_Promotion::has_value')
            )
        );
    }


    /**
     * getAllActiveCodePromotions
     * retrieves all promotions that are currently active based on the current time and do
     * utilize a code
     * Note this DOES include promotions that have no dates set.
     *
     * @param array $query_params
     * @return EE_Promotion[]
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function getAllActiveCodePromotions(array $query_params = []): array
    {
        return $this->get_all(
            array_replace_recursive(
                [
                    [
                        'PRO_code'    => ['!=', null],
                        'PRO_deleted' => false,
                    ],
                ],
                // query params for calendar controlled expiration
                $this->_get_promotion_expiration_query_params(),
                // incoming $query_params array filtered to remove null values and empty strings
                array_filter($query_params, 'EEM_Promotion::has_value')
            )
        );
    }


    /**
     * getAllActiveCodePromotionsForEvent
     * retrieves all promotions that are currently active based on the current time and utilize a code
     * that are either global or assigned to the supplied Event
     * Note this DOES include promotions that have no dates set.
     *
     * @param EE_Event $event
     * @param array    $query_params
     * @return EE_Promotion[]
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function getAllActiveCodePromotionsForEvent(EE_Event $event, array $query_params = []): array
    {
        // IF the promo code is global and has never been redeemed,
        // then there will not be an entry in the esp_promotion_object table yet
        // so then, we need to get the global promotions
        $globals = $this->get_all(
            array_replace_recursive(
                [
                    [
                        'PRO_code'    => ['!=', null],
                        'PRO_scope'   => EE_Promotion_Scope::SCOPE_EVENT,
                        'PRO_deleted' => false,
                        'OR****' => [
                            'PRO_global'  => true,
                            'Promotion_Object.Event.EVT_ID' => $event->ID()
                        ]
                    ],
                ],
                // query params for calendar controlled expiration
                $this->_get_promotion_expiration_query_params(),
                // incoming $query_params array filtered to remove null values and empty strings
                array_filter($query_params, 'EEM_Promotion::has_value')
            )
        );
        return array_unique($globals);
    }


    /**
     * _get_promotion_expiration_query_params
     * query params for calendar controlled expiration
     *
     * @return array
     * @throws EE_Error
     */
    protected function _get_promotion_expiration_query_params(): array
    {
        $promo_start_date = $this->current_time_for_query('PRO_start');
        $promo_end_date   = $this->current_time_for_query('PRO_end');
        return [
            [
                'OR*' => [
                    'AND'   => [
                        'PRO_start' => ['IS NULL'],
                        'PRO_end'   => ['IS NULL'],
                    ],
                    'AND*'    => [
                        'PRO_start*' => ['<=', $promo_start_date],
                        'PRO_end*'   => ['>=', $promo_end_date],
                    ],
                    'AND**'  => [
                        'PRO_start**' => ['<=', $promo_start_date],
                        'PRO_end**'   => ['IS NULL'],
                    ],
                    'AND***' => [
                        'PRO_start***' => ['IS NULL'],
                        'PRO_end***'   => ['>=', $promo_end_date],
                    ],
                ],
            ],
        ];
    }


    /**
     * get_upcoming_codeless_promotions
     * retrieves all promotions that are not active yet but are upcoming within so many days (
     * 60 by default ) and that do not have a code.
     *
     * @param array $query_params
     * @return EE_Promotion[]
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function get_upcoming_codeless_promotions(array $query_params = []): array
    {
        $PRO_end = date(
            'Y-m-d 00:00:00',
            time() + (
                apply_filters(
                    'FHEE__EEM_Promotion__get_upcoming_codeless_promotions__number_of_days',
                    60
                ) * DAY_IN_SECONDS
            )
        );
        return $this->get_all(
            array_replace_recursive(
                [
                    [
                        'AND'         => [
                            'PRO_start' => ['>=', $this->current_time_for_query('PRO_start')],
                            'PRO_end'   => [
                                '<=',
                                $this->convert_datetime_for_query('PRO_end', $PRO_end, 'Y-m-d H:i:s'),
                            ],
                        ],
                        'PRO_code'    => null,
                        'PRO_deleted' => false,
                    ],
                ],
                // incoming $query_params array filtered to remove null values and empty strings
                array_filter($query_params, 'EEM_Promotion::has_value')
            )
        );
    }


    /**
     * Get all active and upcoming promotions that fall within the given range and that do not
     * have a code.
     * Default range is within 60 days from now.
     * Note: this query does NOT return any promotions with no end date.
     *
     * @param array $query_params             any additional query params (or you can replace the
     *                                        defaults as well)
     * @return EE_Promotion[]
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function get_active_and_upcoming_codeless_promotions_in_range(array $query_params = []): array
    {
        $PRO_end = date(
            'Y-m-d 00:00:00',
            time() + (
                apply_filters(
                    'FHEE__EEM_Promotion__get_active_and_upcoming_codeless_promotions_in_range__number_of_days',
                    60
                ) * DAY_IN_SECONDS
            )
        );
        return $this->get_all(
            array_replace_recursive(
                [
                    [
                        'PRO_end'     => [
                            'BETWEEN',
                            [
                                $this->current_time_for_query('PRO_end'),
                                $this->convert_datetime_for_query('PRO_end', $PRO_end, 'Y-m-d H:i:s'),
                            ],
                        ],
                        'PRO_code'    => null,
                        'PRO_deleted' => false,
                    ],
                ],
                // incoming $query_params array filtered to remove null values and empty strings
                array_filter($query_params, 'EEM_Promotion::has_value')
            )
        );
    }


    /**
     * not_null
     *
     * @param $val
     * @return bool
     */
    public static function has_value($val): bool
    {
        return ! ($val === null || $val === '');
    }
}
