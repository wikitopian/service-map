<?php
/*
 * Plugin Name: Service Map
 * Plugin URI: http://www.github.com/wikitopian/service-map
 * Description: Service Map displays an interactive map of locations
 * Author: @wikitopian
 * Version: 0.1
 * License: GPLv3
 * Author URI: http://www.github.com/wikitopian
 */

class Service_Map {

	public function __construct() {
		add_action( 'admin_menu', array( &$this, 'do_menu' ) );
	}

	public function do_menu() {

		add_options_page(
			'Service Map',
			'Service Map',
			'manage_options',
			'service-map',
			array( &$this, 'do_menu_page' )
		);

	}

	public function do_menu_page() {

		echo 'Service Map';

	}

}

$service_map = new Service_Map();

/* EOF */
