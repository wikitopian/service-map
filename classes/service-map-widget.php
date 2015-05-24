<?php

class Service_Map_Widget {

	private $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;

		add_action( 'wp_dashboard_setup', array( &$this, 'do_widget' ) );

	}

	public function do_widget() {

		wp_add_dashboard_widget(
			'service-map-widget',
			'Service Map',
			array( &$this, 'get_widget' )
		);

	}

	public function get_widget() {

		echo 'Service Map';

	}

}

/* EOF */
