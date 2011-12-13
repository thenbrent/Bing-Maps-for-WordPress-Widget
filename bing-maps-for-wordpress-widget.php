<?php
/*
Plugin Name: Bing Maps for WordPress Widget
Plugin URI: 
Description: Builds on the excellent Bing Maps for WordPress plugin by adding a widget for maps.
Author: Brent Shepherd
Version: 1.0
Author URI: 
*/

/**
 * Load files containing backup classes if the required classes are not already registered.
 * 
 * Because of the way that WordPress loads plugins, it’s possible that this plugin could load
 * before or after the Bing Maps for WordPress plugin. This makes it unreliable to check for the 
 * existence of classes when the file is parsed. Checking after all plugins are loaded is more reliable. 
 */
function bmw_loader() {

	if( ! class_exists( 'bingMapsForWordpressContent' ) )
		require_once( 'bing-maps-content.class.php' );

	/* Load the Control Panel class if it's an admin page and not the activation page for the bing-maps-for-wordpress plugin (to avoid redeclaring class) */
	if( ! class_exists( 'bingMapsForWordpressControlPanel' ) && is_admin() && ! ( isset( $_GET['action'] ) && $_GET['action'] == 'activate' && isset( $_GET['plugin'] ) && strpos( $_GET['plugin'], 'bing-maps-for-wordpress' ) !== false ) ) {
		require_once( 'bing-maps-control-panel.class.php' );
		new Bing_Maps_Widget_Control_Panel( plugin_basename( __FILE__ ) );
	}

	require_once( 'widget.class.php' );
}
add_action( 'plugins_loaded', 'bmw_loader' );
