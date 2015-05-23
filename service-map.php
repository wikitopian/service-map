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

require 'service-map-menu.php';

class Service_Map {

	private $settings;

	public function __construct() {

		$default = array(
			'lat' => 39.1000,
			'lng' => -84.5167,
			'zoom' => 4
		);

		$this->settings = get_option( 'service_map_settings', $default );

		$this->menu = new Service_Map_Menu( $this->settings );

	}

}

$service_map = new Service_Map();

/* EOF */
