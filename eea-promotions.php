<?php
/*
  Plugin Name: Event Espresso - Promotions (EE 4.9.10+)
  Plugin URI: http://www.eventespresso.com
  Description: Help promote your events with Event Espresso Promotions by offering discounts. Compatible with Event Espresso 4.9.10 or higher.
  Version: 1.0.10.p
  Author: Event Espresso
  Author URI: http://www.eventespresso.com
  Copyright 2015 Event Espresso (email : support@eventespresso.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA

 * ------------------------------------------------------------------------
 *
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package		Event Espresso
 * @ author			Event Espresso
 * @ copyright	(c) 2008-2015 Event Espresso  All Rights Reserved.
 * @ license		http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link				http://www.eventespresso.com
 * @ version	 	EE4
 *
 * ------------------------------------------------------------------------
 */

define( 'EE_PROMOTIONS_CORE_VERSION_REQUIRED', '4.9.10.rc.004' );
define( 'EE_PROMOTIONS_VERSION', '1.0.10.p' );
define( 'EE_PROMOTIONS_PLUGIN_FILE', __FILE__ );

function load_espresso_promotions() {
	if ( class_exists( 'EE_Addon' )) {
		// promotions_version
		require_once ( plugin_dir_path( __FILE__ ) . 'EE_Promotions.class.php' );
		EE_Promotions::register_addon();
	} else {
		add_action( 'admin_notices', 'espresso_promotions_activation_error' );
	}
}
add_action( 'AHEE__EE_System__load_espresso_addons', 'load_espresso_promotions', 5 );

function espresso_promotions_activation_check() {
	if ( ! did_action( 'AHEE__EE_System__load_espresso_addons' )) {
		add_action( 'admin_notices', 'espresso_promotions_activation_error' );
	}
}
add_action( 'init', 'espresso_promotions_activation_check', 1 );

function espresso_promotions_activation_error() {
	unset( $_GET['activate'] );
	unset( $_REQUEST['activate'] );
	if ( ! function_exists( 'deactivate_plugins' )) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	deactivate_plugins( plugin_basename( EE_PROMOTIONS_PLUGIN_FILE ));
	?>
	<div class="error">
		<p><?php printf( __( 'Event Espresso Promotions could not be activated. Please ensure that Event Espresso version %s or higher is running', 'event_espresso'), EE_PROMOTIONS_CORE_VERSION_REQUIRED ); ?></p>
	</div>
<?php
}

// End of file espresso_promotions.php
// Location: wp-content/plugins/espresso-promotions/espresso_promotions.php
