<?php

class Service_Map_Install {

	private $settings;

	public function __construct( $settings ) {
		$this->settings = $settings;

	}

	public static function do_tables() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}service_map_sites (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			label NVARCHAR(100) NOT NULL,
			street NVARCHAR(100) NOT NULL,
			city NVARCHAR(100) NOT NULL,
			state NVARCHAR(100) NOT NULL,
			zip CHAR(5) NOT NULL,
			lat FLOAT (10,6),
			lng FLOAT (10,6),
			UNIQUE KEY id (id)
			) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}

}

/* EOF */
