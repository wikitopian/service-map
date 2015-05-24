<?php

class Service_Map_Menu {

	public function __construct( $settings ) {
		$this->settings = $settings;

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
		<th scope="row">Google Map API Key:</th>
		<td><input type="text" name="service_map_settings[key]" value="<?php echo esc_attr( $this->settings['key'] ); ?>" /></td>
		</tr>

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

/* EOF */
