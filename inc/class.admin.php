<?php
class RelationsPostTypes_Admin {
	private $admin_url 	= '';
	private $admin_slug = 'relations-posttypes-settings';
	
	// Error management
	private $message = '';
	private $status  = '';
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	function __construct() {
		$this->admin_url = admin_url( 'options-general.php?page='.$this->admin_slug );
		
		// Register hooks
		add_action( 'admin_init', array( &$this, 'checkRelations') );
		add_action( 'admin_init', array( &$this, 'checkImportExport') );
		add_action( 'admin_menu', array( &$this, 'addMenu') );
	}
	

	
	/**
	 * Add settings menu page
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	function addMenu() {
		add_options_page( __('Relations post types', 'relations-post-types'), __('Relations', 'relations-post-types'), 'manage_options', $this->admin_slug, array( &$this, 'pageManage' ) );
	}
	
	/**
	 * Display options on admin
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	function pageManage() {
		// Display message
		$this->displayMessage();
		
		// Current relations
		$current_relations = get_option( RPT_OPTION );
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e("Relations post types : Settings", 'relations-post-types'); ?></h2>
			
			<div class="message updated">
				<p><?php _e('<strong>Warning :</strong> Check or uncheck relations between 2 post types will not delete relations on DB.', 'relations-post-types'); ?></p>
			</div>
			
			<p><?php _e('Instructions for use: lines correspond to each page of edition of the post type, you can show the box of relations with others contents by checking the columns of your choice', 'relations-post-types'); ?></p>
			
			<form action="" method="post">
				<div id="col-container">
					<table class="widefat tag fixed" cellspacing="0">
						<thead>
							<tr>
								<th scope="col" id="label" class="manage-column column-name"><?php _e('Custom types', 'relations-post-types'); ?></th>
								<?php
								foreach ( get_post_types( array(), 'objects' ) as $post_type ) {
									if ( !$post_type->show_ui || empty($post_type->labels->name) )
										continue;
									
									echo '<th scope="col">'.esc_html($post_type->labels->name).'</th>';
								}
								?>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th scope="col" class="manage-column column-name"><?php _e('Custom types', 'relations-post-types'); ?></th>
								<?php
								foreach ( get_post_types( array(), 'objects' ) as $post_type ) {
									if ( !$post_type->show_ui || empty($post_type->labels->name) )
										continue;
									
									echo '<th scope="col">'.esc_html($post_type->labels->name).'</th>';
								}
								?>
							</tr>
						</tfoot>
			
						<tbody id="the-list" class="list:relations">
							<?php
							$class = 'alternate';
							$i = 0;
							foreach ( get_post_types( array(), 'objects' ) as $post_type ) :
								if ( !$post_type->show_ui || empty($post_type->labels->name) )
									continue;
								
								$i++;
								$class = ( $class == 'alternate' ) ? '' : 'alternate';
								?>
								<tr id="custom type-<?php echo $i; ?>" class="<?php echo $class; ?>">
									<th class="name column-name"><?php echo esc_html($post_type->labels->name); ?></th>
									<?php
									foreach ( get_post_types( array(), 'objects' ) as $line_post_type ) {
										if ( !$line_post_type->show_ui || empty($line_post_type->labels->name) )
											continue;

										echo '<td>' . "\n";
											//if ( $line_post_type->name != $post_type->name ) {
												if ( !isset($current_relations[$line_post_type->name]) )
													$current_relations[$line_post_type->name] = array();
													
												echo '<input type="checkbox" name="relations['.$line_post_type->name.']['.$post_type->name.']" value="1" '.checked( true, in_array( $post_type->name, (array) $current_relations[$line_post_type->name] ), false ).' />' . "\n";
											//} else {
											//	echo '-' . "\n";
											//}
										echo '</td>' . "\n";
									}
									?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				
					<p class="submit">
						<?php wp_nonce_field( 'save-relations-settings' ); ?>
						<input class="button-primary" name="save-relations" type="submit" value="<?php _e('Save relations', 'relations-post-types'); ?>" />
					</p>
				</form>
			</div><!-- /col-container -->
		</div>
		
		<div class="wrap">
			<h2><?php _e("Relations post types : Export/Import", 'relations-post-types'); ?></h2>
			
			<a class="button" href="<?php echo wp_nonce_url($this->admin_url.'&amp;action=export_config_rpt', 'export-config-rpt'); ?>"><?php _e("Export config file", 'relations-post-types'); ?></a>
			<a class="button" href="#" id="toggle-import_form"><?php _e("Import config file", 'relations-post-types'); ?></a>
			<script type="text/javascript">
				jQuery("#toggle-import_form").click(function(event) {
					event.preventDefault();
					jQuery('#import_form').removeClass('hide-if-js');
				});
			</script>
			<div id="import_form" class="hide-if-js">
				<form action="<?php echo $this->admin_url ; ?>" method="post" enctype="multipart/form-data">
					<p>
						<label><?php _e("Config file", 'relations-post-types'); ?></label>
						<input type="file" name="config_file" />
					</p>
					<p class="submit">
						<?php wp_nonce_field( 'import_config_file_rpt' ); ?>
						<input class="button-primary" type="submit" name="import_config_file_rpt" value="<?php _e('I want import a config from a previous backup, this action will REPLACE current configuration', 'relations-post-types'); ?>" />
					</p>
				</form>
			</div>
		</div>
		<?php
		return true;
	}
	
	/**
	 * Check $_POST datas for relations liaisons
	 * 
	 * @return boolean
	 */
	function checkRelations() {
		if ( isset($_POST['save-relations']) ) {
			
			check_admin_referer( 'save-relations-settings' );
			
			$relations = array();
			foreach( $_POST['relations'] as $post_type => $values ) {
				foreach( $values as $sub_post_type => $value )
					$relations[$post_type][] = $sub_post_type;
			}
			
			$this->message = __('Relations updated with success !', 'relations-post-types');

			update_option( RPT_OPTION, $relations );
		}
		return false;
	}
	
		/**
	 * Check $_GET/$_POST/$_FILES for Export/Import
	 * 
	 * @return boolean
	 */
	function checkImportExport() {
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
				$this->message = __('An error occured during the config file upload. Please fix your server configuration and retry.', 'relations-post-types');
				$this->status  = 'error';
			} else {
				$config_file = file_get_contents( $_FILES['config_file']['tmp_name'] );
				if ( substr($config_file, 0, strlen('RELATIONSPOSTTYPES')) !== 'RELATIONSPOSTTYPES' ) {
					$this->message = __('This is really a config file for Relations Post Types ? Probably corrupt :(', 'relations-post-types');
					$this->status  = 'error';
				} else {
					$config_file = unserialize(base64_decode(substr($config_file, strlen('RELATIONSPOSTTYPES'))));
					if ( !is_array($config_file) ) {
						$this->message = __('This is really a config file for Relations Post Types ? Probably corrupt :(', 'relations-post-types');
						$this->status  = 'error';
					} else {
						update_option(RPT_OPTION, $config_file);
						$this->message = __('OK. Configuration is restored.', 'relations-post-types');
						$this->status  = 'updated';
					}
				}
			}
		}
	}
	
	/**
	 * Display WP alert
	 *
	 */
	function displayMessage() {
		if ( $this->message != '') {
			$message = $this->message;
			$status = $this->status;
			$this->message = $this->status = ''; // Reset
		}
		
		if ( isset($message) && !empty($message) ) {
		?>
			<div id="message" class="<?php echo ($status != '') ? $status :'updated'; ?> fade">
				<p><strong><?php echo $message; ?></strong></p>
			</div>
		<?php
		}
	}
}