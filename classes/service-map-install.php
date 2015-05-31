<?php

class Service_Map_Install {

	private $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;

		error_log( 'install' );

	}

	public static function do_tables() {

		error_log( 'do_tables' );

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}service_map_sites (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			label NVARCHAR(100) NOT NULL,
			street NVARCHAR(100) NOT NULL,
			city NVARCHAR(100) NOT NULL,
			state NVARCHAR(100) NOT NULL,
			lat FLOAT,
			lng FLOAT,
			UNIQUE KEY id (id)
	) {$charset_collate};";

		error_log( $sql );

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}

}

/* EOF */
