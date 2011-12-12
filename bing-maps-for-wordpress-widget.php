<?php
/*
Plugin Name: Bing Maps for WordPress Widget
Plugin URI: 
Description: Builds on the excellent Bing Maps for WordPress plugin by adding a widget for maps.
Author: Brent Shepherd
Version: 1.0
Author URI: 
*/

if( ! is_admin() ) { // Activate the plugin

	require_once( 'widget.class.php' );

	new bing_maps_widget();
}