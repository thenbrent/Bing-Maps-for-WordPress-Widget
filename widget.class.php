<?php

/**
 * The actual Maps Widget
 */
class Bing_Maps_Widget extends WP_Widget {

	private $bing_maps;

	function __construct() {

		/* Widget settings. */
		$widget_ops = array( 'classname' => 'bing-maps-widget', 'description' => 'A widget to display a bing map.' );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'bing-maps-widget' );

		/* Create the widget. */
		parent::WP_Widget( 'bing-maps-widget', 'Bing Maps Widget', $widget_ops, $control_ops );
	}

	function form( $instance ) {

		$instance = wp_parse_args( $instance, $this->get_default_attributes() ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
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
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_sex'], true ); ?> id="<?php echo $this->get_field_id( 'show_sex' ); ?>" name="<?php echo $this->get_field_name( 'show_sex' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_sex' ); ?>">Display sex publicly?</label>
		</p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$new_instance['title'] = strip_tags( $new_instance['title'] );
		return $new_instance;
	}

	function widget( $args, $instance ) {
		extract( $args );
		$instance = wp_parse_args( $instance, $this->get_default_attributes() );

		$this->bing_maps = new Bing_Maps_Widget_Helper( $instance );

		/* User-selected settings. */
		$instance['title'] = isset( $instance['title'] ) ? $instance['title'] : '';

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( isset( $instance['title'] ) )
			echo $before_title . $instance['title'] . $after_title;

		/* Display map. */
		$this->bing_maps->__displayDynamicMap();

		/* After widget (defined by themes). */
		echo $after_widget;
	}


	function get_default_attributes() {
		return array( 
			'title' => 'Location',
			'locationtitle' => '',
			'locationlink' => '',
			'description' => '',
			'location' => '123 Street, City, State, Country',
			'maptype' => 'Road',
			'zoomDynamic' => '20',
			'type' => 'dynamic'
		);
	}
}

function load_bing_maps_widget() {
	register_widget( 'Bing_Maps_Widget' );
}
add_action( 'widgets_init', 'load_bing_maps_widget' );

