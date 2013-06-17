<?php
class RelationsPostTypes_Admin {
	public static $admin_url 	= '';
	const admin_slug = 'relations-posttypes-settings';
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public function __construct() {
		self::$admin_url = admin_url( 'options-general.php?page='.self::admin_slug );
		
		// Register hooks
		add_action( 'admin_init', array( __CLASS__, 'admin_init') );
		add_action( 'admin_menu', array( __CLASS__, 'add_menu') );
	}
	
	/**
	 * Add settings menu page
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function add_menu() {
		add_options_page( __('Relations post types', 'relations-post-types'), __('Relations', 'relations-post-types'), 'manage_options', self::admin_slug, array( __CLASS__, 'page_settings' ) );
	}
	
	/**
	 * Display options on admin
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function page_settings() {
		// Show error messages
		// settings_errors( RPT_OPTION.'-main' );
		
		// Current relations
		$current_relations = get_option( RPT_OPTION );

		// Current settings
		$current_settings = get_option( RPT_OPTION.'-settings' );
		
		// Get metabox HTML
		include( RPT_DIR . 'views/admin/settings.php' );

		return true;
	}

	public static function admin_init() {
		self::check_settings();
		self::check_import_export();
	}
	
	/**
	 * Check $_POST datas for relations liaisons
	 * 
	 * @return boolean
	 */
	public static function check_settings() {
		if ( isset($_POST['save-relations']) ) {
			check_admin_referer( 'save-relations-settings' );
			
			// Save relations
			$relations = array();
			foreach( $_POST['rpt_relations'] as $post_type => $values ) {
				foreach( $values as $sub_post_type => $value ) {
					$relations[$post_type][] = $sub_post_type;
				}
			}
			update_option( RPT_OPTION, $relations );

			// Cleanup data
			if ( isset($_POST['rpt_settings']['quantity']) ) {
				$_POST['rpt_settings']['quantity'] = array_map("intval", $_POST['rpt_settings']['quantity']);
			}

			// Save settings
			update_option( RPT_OPTION.'-settings', $_POST['rpt_settings'] );

			// Notify users
			add_settings_error( RPT_OPTION.'-main', RPT_OPTION.'-main', __('Relations updated with success !', 'relations-post-types'), 'updated' );

			return true;
		}
		
		return false;
	}

	/**
	 * Check $_GET/$_POST/$_FILES for Export/Import
	 * 
	 * @return boolean
	 */
	public static function check_import_export() {
		if ( isset($_GET['action']) && $_GET['action'] == 'export_config_rpt' ) {
			check_admin_referer('export-config-rpt');
			
			// No cache
			header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); 
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); 
			header( 'Cache-Control: no-store, no-cache, must-revalidate' ); 
			header( 'Cache-Control: post-check=0, pre-check=0', false ); 
			header( 'Pragma: no-cache' ); 
			
			// Force download dialog
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");

			// use the Content-Disposition header to supply a recommended filename and
			// force the browser to display the save dialog.
			header("Content-Disposition: attachment; filename=relations-post-types-config-".date('U').".txt;");
			die('RELATIONSPOSTTYPES'.base64_encode(serialize(get_option( RPT_OPTION ))));
		} elseif( isset($_POST['import_config_file_rpt']) && isset($_FILES['config_file']) ) {
			check_admin_referer( 'import_config_file_rpt' );
			
			if ( $_FILES['config_file']['error'] > 0 ) {
				add_settings_error( RPT_OPTION.'-main', RPT_OPTION.'-main', __('An error occured during the config file upload. Please fix your server configuration and retry.', 'relations-post-types'), 'error' );
			} else {
				$config_file = file_get_contents( $_FILES['config_file']['tmp_name'] );
				if ( substr($config_file, 0, strlen('RELATIONSPOSTTYPES')) !== 'RELATIONSPOSTTYPES' ) {
					add_settings_error( RPT_OPTION.'-main', RPT_OPTION.'-main', __('This is really a config file for Relations Post Types ? Probably corrupt :(', 'relations-post-types'), 'error' );
				} else {
					$config_file = unserialize(base64_decode(substr($config_file, strlen('RELATIONSPOSTTYPES'))));
					if ( !is_array($config_file) ) {
						add_settings_error( RPT_OPTION.'-main', RPT_OPTION.'-main', __('This is really a config file for Relations Post Types ? Probably corrupt :(', 'relations-post-types'), 'error' );
					} else {
						update_option(RPT_OPTION, $config_file);
						add_settings_error( RPT_OPTION.'-main', RPT_OPTION.'-main', __('OK. Configuration is restored.', 'relations-post-types'), 'updated' );;
					}
				}
			}
		}
	}
}