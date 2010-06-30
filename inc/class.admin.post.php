<?php
class RelationPostTypes_Admin_Post {
	/**
	 * Constructor
	 *
	 * @return boolean
	 */
	function RelationPostTypes_Admin_Post() {
		// Save taxo datas
		add_action( 'save_post', array(&$this, 'saveObjectRelations'), 10, 2 );
		
		// Write post box meta
		add_action( 'add_meta_boxes', array(&$this, 'initObjectRelations'), 10, 2 );
		
		return true;
	}
	
	/**
	 * Save relations when save object.
	 *
	 * @param string $post_ID
	 * @param object $post
	 * @return boolean
	 * @author Amaury Balmer
	 */
	function saveObjectRelations( $post_ID = 0, $post = null ) {
		// Take post type from arg !
		$post_type = $post->post_type;
		
		// Prepare admin type for each relations box !
		$current_options = get_option( RPT_OPTION );
		
		// No relations for this post type ?
		if ( !isset($current_options[$post_type] ) )
			return false;
			
		// All tag-style post taxonomies
		foreach ( $current_options[$post_type] as $_post_type ) {
			
			if ( isset($_POST['relations'][$_post_type] ) ) {
				
				if ( $_POST['relations'][$_post_type] === '-' ) { // Use by HTML Select
					
					rpt_delete_object_relation( $post_ID, array($_post_type) );
					
				} else {
					
					// Secure datas
					if ( is_array($_POST['relations'][$_post_type]) )
						$_POST['relations'][$_post_type] = array_map( 'intval', $_POST['relations'][$_post_type] );
					else
						$_POST['relations'][$_post_type] = (int) $_POST['relations'][$_post_type];
					
					rpt_delete_object_relation( $post_ID, array($_post_type) );
					rpt_set_object_relation( $post_ID, $_POST['relations'][$_post_type], $_post_type, false );
					
				}
			}
			
		}
		
		return true;
	}
	
	/**
	 * Add block for each relations in write page for each custom object
	 *
	 * @param string $post_type
	 * @param object $post
	 * @return boolean
	 * @author Amaury Balmer
	 */
	function initObjectRelations( $post_type = '', $post = null ) {
		// Prepare admin type for each relations box !
		$current_options = get_option( RPT_OPTION );
		
		// No relations for this post type ?
		if ( !isset($current_options[$post_type] ) )
			return false;
		
		// All tag-style post taxonomies
		foreach ( $current_options[$post_type] as $_post_type ) {
			
			// Get post type data block
			$_post_type = get_post_type_object( $_post_type );
			
			// Dispatch admin block
			$ad_type = 'default';
			
			// Display meta box
			switch( $ad_type ) {
				/*
				case 'select' : // Custom single selector
					add_meta_box( 'relationsdiv-' . $tax_name, $label, array(&$this, 'post_select_meta_box'), $post_type, 'side', 'default', array( 'post_type' => $_post_type ) );
					break;
				
				case 'select-multi' : // Custom multiple selector
					add_meta_box( 'relationsdiv-' . $tax_name, $label, array(&$this, 'post_select_multi_meta_box'), $post_type, 'side', 'default', array( 'post_type' => $_post_type ) );
					break;
				*/
				
				case 'default' : // Default
				default :
					add_meta_box( 'relationsdiv-' . $_post_type->name, $_post_type->labels->name, array(&$this, 'menu_item_post_type_meta_box'), $post_type, 'side', 'default', $_post_type );
					break;
			}
			
			// Try to free memory !
			unset($_post_type, $ad_type);
			
			return true;
		}
		return false;
	}
	
	/**
	 * Displays a metabox for a post type item.
	 *
	 * @param string $object Not used.
	 * @param string $post_type The post type object.
	 */
	function menu_item_post_type_meta_box( $object, $post_type ) {
		$post_type_name = $post_type['args']->name;

		$args = array(
			'nopaging' 			=> true,
			'order' 			=> 'ASC',
			'orderby' 			=> 'title',
			'post_type' 		=> $post_type_name,
			'post_status' 		=> 'publish',
			'suppress_filters' 	=> true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'current_id' 		=> $object->ID
		);
		
		// For the same post type, exclude current !
		if ( $object->post_type == $post_type_name )
			$args['post__not_in'] = array($object->ID);
		
		// Default ?
		if ( isset( $post_type['args']->_default_query ) )
			$args = array_merge($args, (array) $post_type['args']->_default_query );

		$get_posts = new WP_Query;
		$posts = $get_posts->query( $args );
		if ( ! $get_posts->post_count ) {
			echo '<p>' . __( 'No items.', 'relation-post-types' ) . '</p>';
			return;
		}
		
		$walker = new Walker_Relations_Checklist;
		?>
		<div id="posttype-<?php echo $post_type_name; ?>" class="posttypediv">
			<div id="<?php echo $post_type_name; ?>-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
				<ul id="<?php echo $post_type_name; ?>checklist" class="list:<?php echo $post_type_name; ?> categorychecklist form-no-clear">
					<?php
					$args['walker'] = $walker;
					$checkbox_items = walk_nav_menu_tree( $posts, 0, (object) $args );
					echo $checkbox_items;
					?>
				</ul>
			</div><!-- /.tabs-panel -->
		</div><!-- /.posttypediv -->
		<?php
	}
}
?>