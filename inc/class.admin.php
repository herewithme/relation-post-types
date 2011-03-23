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
	function RelationsPostTypes_Admin() {
		$this->admin_url = admin_url( 'options-general.php?page='.$this->admin_slug );
		
		// Register hooks
		add_action( 'admin_init', array(&$this, 'initStyleScript') );
		add_action( 'admin_init', array(&$this, 'checkRelations') );
		add_action( 'admin_menu', array(&$this, 'addMenu') );
	}
	
	/**
	 * Load JS and CSS need for admin features.
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	function initStyleScript() {
		global $pagenow;
		
		if ( in_array( $pagenow, array('post.php', 'post-new.php') ) ) {
			wp_enqueue_style ( 'simple-custom-types', RPT_URL.'/ressources/admin.css', array(), RPT_VERSION );
		}
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
			
						<tbody id="the-list" class="list:taxonomies">
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
?>