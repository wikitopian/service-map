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

	private $settings;

	public function __construct() {

		$default = array(
			'lat' => 39.1000,
			'lng' => -84.5167,
			'zoom' => 4
		);

		$this->settings = get_option( 'service_map_settings', $default );

		add_action( 'admin_menu', array( &$this, 'do_menu' ) );
		add_action( 'admin_init', array( &$this, 'do_menu_init' ) );

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

	public function do_menu_init() {

		register_setting( 'service_map_settings', 'service_map_settings' );

	}

	public function do_menu_page() {

?>
<div class="wrap">
<h2>Service Map</h2>

<form method="post" action="options.php">
	<?php settings_fields( 'service_map_settings' ); ?>
	<?php do_settings_sections( 'service_map_settings' ); ?>
	<table class="form-table">

		<tr valign="top">
		<th scope="row">Latitude:</th>
		<td><input type="text" name="service_map_settings[lat]" value="<?php echo esc_attr( $this->settings['lat'] ); ?>" /></td>
		</tr>

		<tr valign="top">
		<th scope="row">Longitude:</th>
		<td><input type="text" name="service_map_settings[lng]" value="<?php echo esc_attr( $this->settings['lng'] ); ?>" /></td>
		</tr>

		<tr valign="top">
		<th scope="row">Zoom Level:</th>
		<td><input type="text" name="service_map_settings[zoom]" value="<?php echo esc_attr( $this->settings['zoom'] ); ?>" /></td>
		</tr>

	</table>

	<?php submit_button(); ?>

</form>
</div>

<?php

	}

}

$service_map = new Service_Map();

/* EOF */
