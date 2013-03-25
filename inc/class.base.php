<?php
class RelationsPostTypes_Base {
	/**
	 * Empty constructor.
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public function __construct() {}
	
	/**
	 * Try to create the table during the installation
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function activate() {
		global $wpdb;
		
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";

		// Add one library admin function for next function
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Try to create the meta table
		return maybe_create_table( $wpdb->posts_relations, "CREATE TABLE $wpdb->posts_relations (
				`id` int(20) NOT NULL auto_increment,
				`object_id_1` INT( 20 ) NOT NULL,
				`object_id_2` INT( 20 ) NOT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `object_ids` (`object_id_1`,`object_id_2`)
			) $charset_collate;" );
	}
	
	/**
	 * Empty function for callback uninstall
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function deactivate() {}
}