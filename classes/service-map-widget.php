<?php

class Service_Map_Widget {

	private $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;

		add_action( 'wp_dashboard_setup', array( &$this, 'do_widget' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'do_scripts' ) );

	}

	public function do_widget() {

		wp_add_dashboard_widget(
			'service-map-widget',
			'Service Map',
			array( &$this, 'get_widget' )
		);

	}

	public function get_widget() {

		echo '<div id="map-canvas"></div>';

	}

	public function do_scripts( $hook ) {

		// only load map on main widget page
		if( $hook != 'index.php' ) {
			return;
		}

		wp_enqueue_script(
			'service-map',
			plugin_dir_url( __FILE__ ) . '../scripts/service-map.js'
		);

		wp_enqueue_script(
			'service-map-google',
			'https://maps.googleapis.com/maps/api/js?key=' . $this->settings['key'],
			null,
			null
		);

		wp_localize_script(
			'service-map',
			'service_map',
			array(
				'lat' => $this->settings['lat'],
				'lng' => $this->settings['lng'],
				'zoom' => $this->settings['zoom'],
				'items' => $this->get_items()
			)
		);

		wp_enqueue_style(
			'service-map',
			plugin_dir_url( __FILE__ ) . '../styles/service-map.css'
		);

	}

	public function get_items() {

		return array(
			'lorem' => 'ipsum'
		);

	}

}

/* EOF */
