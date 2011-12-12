<?php
/*
Plugin Name: Bing Maps for WordPress Widget
Plugin URI: 
Description: Builds on the excellent Bing Maps for WordPress plugin by adding a widget for maps.
Author: Brent Shepherd
Version: 1.0
Author URI: 
*/

require_once( 'bing-maps-content.class.php' );

/**
 * Extends the bingMapsForWordpressContent class to include a widget 
 * for displaying maps. 
 */
class Bing_Maps_Widget_Helper extends bingMapsForWordpressContent {
	
	/**
	 * Creates the widget class
	 */
	function __construct( $attributes ) {

		// Get options
		$options = get_option( 'bing_maps_for_wordpress' );

		$this->atts = $attributes;

		// Only run if we have an API key
		if( isset( $options['api'] ) AND $options['api'] != '' ) {
			// Header action - to load up Bing maps JavaScript
			add_action( 'wp_head', array( $this, '__header' ) );

			// Set the API key
			$this->apiKey = $options['api'];
		}
	}
}

