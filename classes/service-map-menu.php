<?php

class Service_Map_Menu {

	public function __construct( $settings ) {
		$this->settings = $settings;

		add_action( 'admin_menu', array( &$this, 'do_menu' ) );
		add_action( 'admin_init', array( &$this, 'do_menu_init' ) );

		add_action( 'admin_enqueue_scripts', array( &$this, 'do_scripts' ) );

	}

	public function do_menu() {

		add_menu_page(
			'Service Map',
			'Service Map',
			'manage_options',
			'service-map',
			array( &$this, 'do_menu_page' ),
			plugin_dir_url( __FILE__ ) . '../graphics/service-map.png',
			2.5
		);

		add_submenu_page(
			'service-map',
			'Manage Sites',
			'Manage',
			'manage_options',
			'service-map-manage',
			array( &$this, 'do_menu_page_manage' )
		);

		add_submenu_page(
			'service-map',
			'Service Map Settings',
			'Settings',
			'manage_options',
			'service-map-settings',
			array( &$this, 'do_menu_page_settings' )
		);

	}

	public function do_menu_init() {

		register_setting( 'service_map_settings', 'service_map_settings' );

	}

	public function do_scripts( $hook ) {

		// only load map on main widget page
		if( !preg_match( '/service-map/', $hook ) ) {
			return;
		}

		wp_enqueue_script(
			'service-map',
			plugin_dir_url( __FILE__ ) . '../scripts/service-map.js',
			array( 'jquery' )
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
			)
		);

		wp_enqueue_style(
			'service-map',
			plugin_dir_url( __FILE__ ) . '../styles/service-map.css'
		);

	}


	public function do_menu_page() {

		echo '<div id="map-canvas"></div>';

	}

	public function do_menu_page_manage() {

		echo 'table';

	}

	public function do_menu_page_settings() {

		$options = array(
			'key'  => 'Google API Key',
			'lat'  => 'Latitude',
			'lng'  => 'Longitude',
			'zoom' => 'Zoom Level',
		);

?>
<div class="wrap">
<h2>Service Map</h2>

<form method="post" action="options.php">
	<?php settings_fields( 'service_map_settings' ); ?>
	<?php do_settings_sections( 'service_map_settings' ); ?>
	<table class="form-table">

	<?php foreach( $options as $option => $label ) { ?>

		<tr valign="top">
		<th scope="row"><?php echo $label; ?></th>
			<td>
				<input
					type="text"
					name="service_map_settings[<?php echo $key ?>]"
					value="<?php echo esc_attr( $this->settings['key'] ); ?>"
					/>
			</td>
		</tr>

	<?php } ?>

	</table>

	<?php submit_button(); ?>

</form>
</div>

<?php

	}

}

/* EOF */
