<?php
class RelationsPostTypes_Admin_Post {
	/**
	 * Constructor
	 *
	 * @return boolean
	 */
	function __construct() {
		// Metabox
		add_action( 'save_post', array(__CLASS__, 'save_post'), 10, 2 );
		add_action( 'add_meta_boxes', array(__CLASS__, 'add_meta_boxes'), 10, 2 );

		// The ajax action for the serach in boxes
		add_action( 'wp_ajax_posttype-quick-search', array( __CLASS__, 'wp_ajax_posttype_quick_search' ) );
		
		// Register JS/CSS
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts') );
		
		return true;
	}
	
	/**
	 * Load JS and CSS need for admin features.
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function admin_enqueue_scripts( $hook ) {
		if ( in_array( $hook, array('post.php', 'post-new.php') ) ) {
			wp_enqueue_script ( 'rpt-admin-post', RPT_URL.'/ressources/js/admin-post.min.js', 'jquery', RPT_VERSION, true );
			wp_localize_script( 'rpt-admin-post', 'rpt', array( 'noItems' => __( 'No results found.', 'relation-post-type' ) ) ); // Add javascript translation
		}
	}
	
	/**
	 * Save relations when save object.
	 *
	 * @param string $post_ID
	 * @param object $post
	 * @return boolean
	 * @author Amaury Balmer
	 */
	public static function save_post( $post_ID = 0, $post = null ) {
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
	public static function add_meta_boxes( $post_type = '', $post = null ) {
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
						add_meta_box( 'relationsdiv-' . $tax_name, $label, array(__CLASS__, 'post_select_meta_box'), $post_type, 'side', 'default', array( 'post_type' => $_post_type ) );
						break;
					
					case 'select-multi' : // Custom multiple selector
						add_meta_box( 'relationsdiv-' . $tax_name, $label, array(__CLASS__, 'post_select_multi_meta_box'), $post_type, 'side', 'default', array( 'post_type' => $_post_type ) );
						break;
					*/
					
					case 'default' : // Default
					default :
						add_meta_box( 'relationsdiv-' . $current_post_type->name, $current_post_type->labels->name, array(__CLASS__, 'metabox'), $post_type, 'side', 'default', $current_post_type );
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
	public static function metabox( $object, $post_type ) {
		// Take post type name from metabox args
		$post_type_name = $post_type['args']->name;

		// Current settings
		$current_settings = get_option( RPT_OPTION.'-settings' );

		// Default value if not set
		$qty_value = ( isset($current_settings['quantity'][$post_type_name]) ) ? (int) $current_settings['quantity'][$post_type_name] : 0;

		// Get current items for checked datas.
		$current_items = rpt_get_object_relation( $object->ID );
		if ( is_array($current_items) ) {
			$current_items = array_map( 'intval', $current_items );
		}
		
		// Build args for walker
		$args = array(
			'order' 			=> 'ASC',
			'orderby' 			=> 'title',
			'post_type' 		=> $post_type_name,
			'post_status' 		=> 'publish',
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'current_id' 		=> $object->ID,
			'current_items'		=> $current_items,
			'rpt_query'			=> true
		);

		// Quantity ?
		if ( (int) $qty_value > 0 ) {
			$args['posts_per_page'] = (int) $qty_value;
		} else {
			$args['posts_per_page'] = 0;
			$args['nopaging'] = true;
		}
		
		// For the same post type, exclude current !
		if ( $object->post_type == $post_type_name ) {
			$args['post__not_in'] = array($object->ID);
		}
		
		// Default ?
		if ( isset( $post_type['args']->_default_query ) ) {
			$args = array_merge($args, (array) $post_type['args']->_default_query );
		}

		// Get datas
		$items_query = new WP_Query( $args );
		if ( !$items_query->have_posts() ) {
			echo '<p>' . __( 'No results found.', 'relation-post-types' ) . '</p>';
			return false;
		}

		// The current tab selected
		$current_tab = 'all';
		if ( isset( $_REQUEST[$post_type_name . '-tab'] ) && in_array( $_REQUEST[$post_type_name . '-tab'], array('all', 'search') ) ) {
			$current_tab = $_REQUEST[$post_type_name . '-tab'];
		}

		// check if we are searching
		if ( ! empty( $_REQUEST['quick-search-posttype-' . $post_type_name] ) ) {
			$current_tab = 'search';
		}

		// Create the walker
		$walker = new Walker_Relations_Checklist();

		// Get metabox HTML
		include( RPT_DIR . 'views/admin/metabox.php' );

		return true;
	}

	/**
	 * Prints the appropriate response to a posttype quick search.
	 *
	 *
	 * @param void
	 * @author Nicolas Juen
	 */
	public static function wp_ajax_posttype_quick_search( ) {
		$args = array();
		$type = isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : '';
		$query = isset( $_REQUEST['q'] ) ? $_REQUEST['q'] : '';
		$response_format = isset( $_REQUEST['response-format'] ) && in_array( $_REQUEST['response-format'], array( 'json', 'markup' ) ) ? $_REQUEST['response-format'] : 'json';
		$post_id = isset( $_REQUEST['post_id'] ) ? (int)$_REQUEST['post_id'] : '';
		
		// Ad custom walker
		if ( 'markup' == $response_format ) {
			$args['walker'] = new Walker_Relations_Checklist();
		}
		
		if( (int) $post_id > 0 ) {
			// Get current items for checked datas.
			$current_items = rpt_get_object_relation( $post_id );
			if ( is_array($current_items) )
				$current_items = array_map( 'intval', $current_items );
		
			$args['current_items'] = $current_items;
			$args['current_id'] = $post_id;
		}
		
		$matches = array();
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
					'rpt_query'		=> true
				));
				
				if ( ! have_posts() ) {
					return false;
				}

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