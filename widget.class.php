<?php

/**
 * The actual Maps Widget
 */
class Bing_Maps_Widget extends WP_Widget {

	private $bing_maps;

	function Bing_Maps_Widget() {

		/* Widget settings. */
		$widget_ops = array( 'classname' => 'bing-maps-widget', 'description' => 'A widget to display a bing map.' );

		/* Create the widget. */
		parent::WP_Widget( 'bing-maps-widget', 'Bing Maps Widget', $widget_ops );

		/* Load Bing Map scripts when this widget is active */
		if( is_active_widget( false, false, $this->id_base, true ) ) {
			/* Get the is_plugin_active() function */
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			/* Don't add script twice */
			if( ! is_plugin_active( 'bing-maps-for-wordpress/bing-maps-for-wordpress.php' ) )
				add_action( 'wp_head', array( &$this, 'add_script' ) );
		}
	}

	function form( $instance ) {

		$instance = wp_parse_args( $instance, $this->get_default_attributes() ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Widget Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'locationtitle' ); ?>">Push Pin Title:</label>
			<input id="<?php echo $this->get_field_id( 'locationtitle' ); ?>" name="<?php echo $this->get_field_name( 'locationtitle' ); ?>" value="<?php echo $instance['locationtitle']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'location' ); ?>">Address:</label>
			<input id="<?php echo $this->get_field_id( 'location' ); ?>" name="<?php echo $this->get_field_name( 'location' ); ?>" value="<?php echo $instance['location']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'maptype' ); ?>">Map Type:</label>
			<select id="<?php echo $this->get_field_id( 'maptype' ); ?>" name="<?php echo $this->get_field_name( 'maptype' ); ?>" class="widefat" style="width:100%;">
				<option <?php if ( 'road' == $instance['maptype'] ) echo 'selected="selected"'; ?> value="road">Road</option>
				<option <?php if ( 'aerial' == $instance['maptype'] ) echo 'selected="selected"'; ?> value="aerial">Aerial</option>
				<option <?php if ( 'aerialwithlabels' == $instance['maptype'] ) echo 'selected="selected"'; ?> value="aerialwithlabels">Hybrid</option>
				<option <?php if ( 'birdseye' == $instance['maptype'] ) echo 'selected="selected"'; ?> value="birdseye">Birdseye</option>
			</select>
		</p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$new_instance['title'] = strip_tags( $new_instance['title'] );

		/* If we have a new location, so get it's co-ordinates */
		if( $old_instance['location'] != $new_instance['location'] ) {
			$this->bing_maps = new Bing_Maps_Widget_Helper( $new_instance );

			/* Resolve input - might not have the lat/long yet */
			$location_lookup = $this->bing_maps->__resolveLocation( rawurlencode( $this->bing_maps->atts['location'] ) );

			if( $location_lookup ) {
				$new_instance['lat'] = $location_lookup['lat'];
				$new_instance['long'] = $location_lookup['long'];
			}

		} else {
			$new_instance['lat'] = $old_instance['lat'];
			$new_instance['long'] = $old_instance['long'];
		}

		return $new_instance;
	}

	function widget( $args, $instance ) {
		extract( $args );
		$instance = wp_parse_args( $instance, $this->get_default_attributes() );

		/* Create a Bing Maps object */
		$this->bing_maps = new Bing_Maps_Widget_Helper( $instance );

		/* User-selected settings. */
		$instance['title'] = isset( $instance['title'] ) ? $instance['title'] : '';

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( isset( $instance['title'] ) )
			echo $before_title . $instance['title'] . $after_title;

		/* Display map. */
		echo $this->bing_maps->__displayDynamicMap();

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Load the Bing map controls JavaScript
	 */
	function add_script() {
		echo '<script type="text/javascript" src="http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.3"></script>';
	}

	function get_default_attributes() {
		return array( 
			'title' => '',
			'locationtitle' => '',
			'locationlink' => '',
			'description' => '',
			'location' => '123 Street, City, State, Country',
			'maptype' => 'road',
			'zoomDynamic' => '20',
			'width' => '300',
			'height' => '260',
			'type' => 'dynamic'
		);
	}
}


/**
 * Registers the Bing_Maps_Widget with WordPress.
 */
function load_bing_maps_widget() {
	register_widget( 'Bing_Maps_Widget' );
}
add_action( 'widgets_init', 'load_bing_maps_widget' );


/**
 * Extends the bingMapsForWordpressContent class to include a widget 
 * for displaying maps. 
 */
class Bing_Maps_Widget_Helper extends bingMapsForWordpressContent {

	/**
	 * Creates the widget class
	 */
	function __construct( $attributes ) {

		if( isset( $attributes['lat'] ) ) 
			$this->lat = $attributes['lat'];
		if( isset( $attributes['long'] ) ) 
			$this->long = $attributes['long'];;

		$this->atts = $attributes;

		// Get options
		$options = get_option( 'bing_maps_for_wordpress' );

		// Only run if we have an API key
		if( isset( $options['api'] ) AND $options['api'] != '' ) {
			// Set the API key
			$this->apiKey = $options['api'];
		}
	}
}

