<?php
class RelationsPostTypes_Admin_Post {
	/**
	 * Constructor
	 *
	 * @return boolean
	 */
	function __construct() {
		// Save taxo datas
		add_action( 'save_post', array(&$this, 'saveObjectRelations'), 10, 2 );
		
		// Write post box meta
		add_action( 'add_meta_boxes', array(&$this, 'initObjectRelations'), 10, 2 );
		
		// The ajax action for the serach in boxes
		add_action( 'wp_ajax_posttype-quick-search', array( &$this, 'wp_ajax_posttype_quick_search' ) );
		
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
		// Don't do anything when autosave 
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
				return false;
		
		// Save only when relation post type is called before.
		if ( !isset($_POST['post-relation-post-types']) || $_POST['post-relation-post-types'] != 1 )
			return false;
		
		// Get current post object
		if ( !isset($post) || is_null($post) )
			$post = get_post( $post_ID );

		// Take post type from arg !
		$post_type = $post->post_type;
		
		// Prepare admin type for each relations box !
		$current_options = get_option( RPT_OPTION );
		
		// All tag-style post taxonomies
		foreach ( (array) $current_options as $current_post_type => $_post_types ) {
			foreach( (array) $_post_types as $_post_type ) {
				if ( $_post_type != $post_type )
					continue;

				if ( isset($_POST['action']) && !isset($_POST['relations'][$current_post_type]) ) {
					
					rpt_delete_object_relation( $post_ID, array($current_post_type) );
					
				} elseif ( isset($_POST['relations'][$current_post_type]) ) {
					
					// Secure datas
					if ( is_array($_POST['relations'][$current_post_type]) ) {
						$_POST['relations'][$current_post_type] = array_map( 'intval', $_POST['relations'][$current_post_type] );
						$_POST['relations'][$current_post_type] = array_unique( $_POST['relations'][$current_post_type] );
					} else
						$_POST['relations'][$current_post_type] = (int) $_POST['relations'][$current_post_type];
					
					rpt_set_object_relation( $post_ID, $_POST['relations'][$current_post_type], $current_post_type, false );
				
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

		// All tag-style post taxonomies
		foreach ( (array) $current_options as $current_post_type => $_post_types ) {
			foreach( (array) $_post_types as $_post_type ) {
				if ( $_post_type != $post_type )
					continue;
					
				// Get post type data block
				$current_post_type = get_post_type_object( $current_post_type );
				
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
						add_meta_box( 'relationsdiv-' . $current_post_type->name, $current_post_type->labels->name, array(&$this, 'menu_item_post_type_meta_box'), $post_type, 'side', 'default', $current_post_type );
						break;
				}
				
				// Try to free memory !
				unset($_post_type, $ad_type);
			
			}
		}
		
		return true;
	}
	
	/**
	 * Displays a metabox for a post type item.
	 *
	 * @param string $object Not used.
	 * @param string $post_type The post type object.
	 * @author Amaury Balmer , Nicolas Juen
	 */
	function menu_item_post_type_meta_box( $object, $post_type ) {
		$post_type_name = $post_type['args']->name;

		// Get current items for checked datas.
		$current_items = rpt_get_object_relation( $object->ID );
		if ( is_array($current_items) )
			$current_items = array_map( 'intval', $current_items );
		
		// Build args for walker
		$args = array(
			'nopaging' 			=> true,
			'order' 			=> 'ASC',
			'orderby' 			=> 'title',
			'post_type' 		=> $post_type_name,
			'post_status' 		=> 'publish',
			'suppress_filters' 	=> true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'current_id' 		=> $object->ID,
			'current_items'		=> $current_items
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
			echo '<p>' . __( 'No results found.', 'relation-post-types' ) . '</p>';
			return;
		}
		
		// Most related objects
		// Get objects ids
		$mostRelatedIds = rpt_get_objects_most_used( $post_type_name );
		
		// Change the original args of the query
		$args['post__in'] = $mostRelatedIds;
		$args['orderby'] = null;
		$args['order'] = null;
		
		// Create new object query
		$mQuery = new WP_Query;
		$mPosts = $get_posts->query( $args );

		// The current ab selected
		$current_tab = 'all';
		if ( isset( $_REQUEST[$post_type_name . '-tab'] ) && in_array( $_REQUEST[$post_type_name . '-tab'], array('all', 'search') ) ) {
			$current_tab = $_REQUEST[$post_type_name . '-tab'];
		}
		
		// check if we are seraching
		if ( ! empty( $_REQUEST['quick-search-posttype-' . $post_type_name] ) ) {
			$current_tab = 'search';
		}
		//Create the walker
		$walker = new Walker_Relations_Checklist;
		?>
		<div id="posttype-<?php echo $post_type_name; ?>" class="nav-menus-php posttypediv">
			<ul id="posttype-<?php echo $post_type_name; ?>-tabs" class="posttype-tabs add-menu-item-tabs">
				<li <?php echo ( 'all' == $current_tab ? ' class="tabs"' : '' ); ?>><a class="nav-tab-link" href="#tabs-panel-posttype-<?php echo $post_type_name; ?>-all"><?php _e( 'View All', 'relation-post-types' ); ?></a></li>
				<li <?php echo ( 'most-recent' == $current_tab ? ' class="tabs"' : '' ); ?>><a class="nav-tab-link" href="#tabs-panel-posttype-<?php echo $post_type_name; ?>-most-recent"><?php _e( 'Most Used', 'relation-post-types' ); ?></a></li>
				<li <?php echo ( 'search' == $current_tab ? ' class="tabs"' : '' ); ?>><a class="nav-tab-link" href="#tabs-panel-posttype-<?php echo $post_type_name; ?>-search"><?php _e( 'Search', 'relation-post-types' ); ?></a></li>
			</ul>
			
			<div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-all" class="tabs-panel tabs-panel-view-all <?php echo ( 'all' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>">
				<ul id="<?php echo $post_type_name; ?>checklist" class="list:<?php echo $post_type_name; ?> categorychecklist form-no-clear">
					<?php
					$args['walker'] = $walker;
					$checkbox_items = walk_nav_menu_tree( $posts, 0, (object) $args );
					echo $checkbox_items;
					?>
				</ul>
			</div><!-- /.tabs-all -->
			
			<div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-most-recent" class="tabs-panel tabs-panel-view-all <?php echo ( 'most-recent' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>">
				<ul id="<?php echo $post_type_name; ?>checklist" class="list:<?php echo $post_type_name; ?> categorychecklist form-no-clear">
					<?php
					$args['walker'] = $walker;
					$checkbox_items = walk_nav_menu_tree( $mPosts, 0, (object) $args );
					echo $checkbox_items;
					?>
				</ul>
			</div><!-- /.tabs-most-recent -->

			<div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-search" class="tabs-panel <?php echo ( 'search' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>" >
				<?php
				if ( isset( $_REQUEST['quick-search-posttype-' . $post_type_name] ) ) {
					$searched = esc_attr( $_REQUEST['quick-search-posttype-' . $post_type_name] );
					$search_results = get_posts( array( 's' => $searched, 'post_type' => $post_type_name, 'fields' => 'all', 'order' => 'DESC', ) );
				} else {
					$searched = '';
					$search_results = array();
				}
				?>
				<p class="quick-search-wrap">
					<input type="text" class="quick-search input-with-default-title" title="<?php esc_attr_e('Search'); ?>" value="<?php echo $searched; ?>" name="quick-search-posttype-<?php echo $post_type_name; ?>" />
					<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
					<?php submit_button( __( 'Search', 'relation-post-types' ), 'quick-search-submit button-secondary hide-if-js', 'submit', false, array( 'id' => 'submit-quick-search-posttype-' . $post_type_name ) ); ?>
				</p>
	
				<ul id="<?php echo $post_type_name; ?>-search-checklist" class="list:<?php echo $post_type_name?> categorychecklist form-no-clear">
				<?php if ( ! empty( $search_results ) && ! is_wp_error( $search_results ) ) : ?>
					<?php
					$args['walker'] = $walker;
					echo walk_nav_menu_tree( $search_results, 0, (object) $args );
					?>
				<?php elseif ( is_wp_error( $search_results ) ) : ?>
					<li><?php echo $search_results->get_error_message(); ?></li>
				<?php elseif ( ! empty( $searched ) ) : ?>
					<li><?php _e( 'No results found.', 'relation-post-types' ); ?></li>
				<?php endif; ?>
				</ul>
			</div><!-- /.tabs-search -->
			
		</div><!-- /.posttypediv -->
		
		<input type="hidden" name="post-relation-post-types" value="1" />
		<?php
	}

	/**
	 * Prints the appropriate response to a posttype quick search.
	 *
	 *
	 * @param void
	 * @author Nicolas Juen
	 */
	function wp_ajax_posttype_quick_search( ) {
		$args = array();
		$type = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : '';
		$query = isset( $_REQUEST['q'] ) ? $_REQUEST['q'] : '';
		$response_format = isset( $_REQUEST['response-format'] ) && in_array( $_REQUEST['response-format'], array( 'json', 'markup' ) ) ? $_REQUEST['response-format'] : 'json';
		$post_id = isset( $_REQUEST['post_id'] ) ? (int)$_REQUEST['post_id'] : '';
		
		// Ad custom walker
		if ( 'markup' == $response_format ) {
			$args['walker'] = new Walker_Relations_Checklist;
		}
		if( (int)$post_id > 0 ) {
			// Get current items for checked datas.
			$current_items = rpt_get_object_relation( $post_id );
			if ( is_array($current_items) )
				$current_items = array_map( 'intval', $current_items );
		
			$args['current_items'] = $current_items;
			$args['current_id'] = $post_id;
		}
		
		// Get current items for checked datas.
		$current_items = rpt_get_object_relation( $object->ID );
		if ( is_array($current_items) )
			$current_items = array_map( 'intval', $current_items );

		if ( preg_match('/quick-search-(posttype|taxonomy)-([a-zA-Z_-]*\b)/', $type, $matches) ) {
			if ( 'posttype' == $matches[1] && get_post_type_object( $matches[2] ) ) {
				query_posts(array(
					'nopaging' 		=> true,
					'post_type' 	=> $matches[2],
					'post_status' 	=> 'publish',
					'order' 		=> 'ASC',
					'orderby' 		=> 'title',
					's' 			=> $query,
					'post__not_in' 	=> array( $post_id ),
				));
				if ( ! have_posts() )
					return;
				while ( have_posts() ) {
					the_post();
					if ( 'markup' == $response_format ) {
						// Add post_type for the walker
						$args['post_type'] = $matches[2];
						echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', array( get_post( get_the_ID() ) ) ), 0, (object) $args );
					} elseif ( 'json' == $response_format ) {
						echo json_encode(
							array(
								'ID' => get_the_ID(),
								'post_title' => get_the_title(),
								'post_type' => get_post_type(),
							)
						);
						echo "\n";
					}
				}
			}
		}
		die();
	}
}
?>