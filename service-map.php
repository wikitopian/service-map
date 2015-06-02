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

require 'classes/service-map-install.php';
require 'classes/service-map-menu.php';
require 'classes/service-map-widget.php';

class Service_Map {

	private $settings;

	private $menu;
	private $widget;

	public function __construct() {

		$default = array(
			'key' => '',
			'lat' => 39.1000,
			'lng' => -84.5167,
			'zoom' => 4
		);

		$this->settings = get_option( 'service_map_settings', $default );

		$this->install = new Service_Map_Install( $this->settings );
		$this->menu   =  new Service_Map_Menu(    $this->settings );
		$this->widget =  new Service_Map_Widget(  $this->settings );

		add_action( 'wp_ajax_get_sites', array( &$this, 'get_sites' ) );

	}

	public function get_sites() {

		$bounds = array();

		$bounds['lat']['ne'] = floatval( $_POST['bounds']['ne_lat'] );
		$bounds['lng']['ne'] = floatval( $_POST['bounds']['ne_lng'] );
		$bounds['lat']['sw'] = floatval( $_POST['bounds']['sw_lat'] );
		$bounds['lng']['sw'] = floatval( $_POST['bounds']['sw_lng'] );

		foreach( $bounds['lat'] as $latitude ) {
			if( !$this->validate_latitude( $latitude ) ) {
				wp_die( 'invalid latitude' );
			}
		}

		foreach( $bounds['lng'] as $longitude ) {
			if( !$this->validate_longitude( $longitude ) ) {
				wp_die( 'invalid longitude' );
			}
		}

		global $wpdb;

		$lat_min = MIN( $bounds['lat']['ne'], $bounds['lat']['sw'] );
		$lat_max = MAX( $bounds['lat']['ne'], $bounds['lat']['sw'] );
		$lng_min = MIN( $bounds['lng']['ne'], $bounds['lng']['sw'] );
		$lng_max = MAX( $bounds['lng']['ne'], $bounds['lng']['sw'] );


		$query = <<<QUERY

SELECT
	*
	FROM {$wpdb->prefix}service_map_sites AS sites
	WHERE sites.lat BETWEEN %f AND %f
	  AND sites.lng BETWEEN %f AND %f

QUERY;

		$query = $wpdb->prepare(
			$query,
			$lat_min,
			$lat_max,
			$lng_min,
			$lng_max
		);

		$results = $wpdb->get_results( $query, OBJECT_K );

		echo wp_json_encode( $results );

		wp_die();

	}

	public static function validate_latitude( $latitude ) {

		if( !is_float( $latitude ) ) {
			return( false );
		}

		if( $latitude > 90.0 || $latitude < -90.0 ) {
			return( false );
		}

		return( true );

	}

	public static function validate_longitude( $longitude ) {

		if( !is_float( $longitude ) ) {
			return( false );
		}

		if( $longitude > 180.0 || $longitude < -180.0 ) {
			return( false );
		}

		return( true );

	}



}

register_activation_hook(
	__FILE__,
	array( 'Service_Map_Install', 'do_tables' )
);

$service_map = new Service_Map();

/* EOF */
