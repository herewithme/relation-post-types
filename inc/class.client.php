<?php
class RelationsPostTypes_Client {
	/**
	 * Constructor, register hooks
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public function __construct() {
		// Translation
		add_filter( 'init', array(__CLASS__, 'init') );

		// Add query vars, for allow filtering with URL
		add_filter( 'query_vars', array(__CLASS__, 'query_vars') );
		add_action( 'parse_query', array(__CLASS__, 'parse_query') );
		
		// Delete post
		add_action( 'delete_post', array(__CLASS__, 'delete_post') );

		// WP_Query
		add_action( 'posts_results', array(__CLASS__, 'posts_results'), 10, 2 );
	}

	// Load translations
	public static function init() {	
		load_plugin_textdomain('relations-post-types', false, basename(RPT_DIR) . '/languages');
	}

	public static function posts_results( $posts, $query ) {
		$current_items = $query->get('current_items');
		if( !empty($current_items) && $query->get('posts_per_page') > 0 && $query->get('nopaging') == false ) {
			// Get IDS from objects
			$post_ids = wp_list_pluck( $posts, 'ID' );

			// Get ID not in two arrays
			$post_ids_to_get = array_diff($current_items, $post_ids);
			
			// Get objects
			$_posts = self::get_objects( $post_ids_to_get, $query->query_vars );

			// Merge additional posts
			$posts = array_merge( $_posts, $posts );
		}

		return $posts;
	}

	public static function get_objects( $ids = array(), $args = array() ) {
		if ( empty($ids) ) {
			return array();
		}

		// Customize some element for args
		$args['suppress_filters'] = true;
		$args['post__in'] = $ids;
		$args['nopaging'] = true;
		unset($args['posts_per_page'], $args['current_items']);

		// Get posts
		$get_query = new WP_Query($args);

		return $get_query->posts;
	}
	
	/**
	 * Hook call for delete relation of deleted post
	 *
	 * @param integer $post_id 
	 * @return void
	 * @author Amaury Balmer
	 */
	public static function delete_post( $post_id = 0 ) {
		$type = get_post_type( $post_id );
		rpt_delete_object_relation( (int) $post_id, array($type) );
	}
	
	/**
	 * Add key words relations on search query vars array
	 */
	public static function query_vars( $query_vars = array() ) {
		foreach ( get_post_types( array('show_ui' => true, 'public' => true), 'objects' ) as $post_type ) {
			$query_vars[] = 'rel-'.$post_type->name;
		}
		
		return $query_vars;
	}
	
	/**
	 * Filtering results when rel- key word is set on WP_Query
	 */
	public static function parse_query( $query ) {
		global $wpdb;
		
		foreach ( get_post_types( array('show_ui' => true, 'public' => true), 'objects' ) as $post_type ) {
			$key = 'rel-'.$post_type->name;
			
			if ( isset($query->query_vars[$key]) && !empty($query->query_vars[$key]) ) {
				// Get ID form slug
				$post_id = (int) $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s", stripslashes($query->query_vars[$key]), $post_type->name) );
				
				if ( $post_id == 0 ) { // Post not exist for this post name ? Make zero result
					$query->query_vars['post__in'] = array(0);
				} else { // Otherwise, filtering result with this ID.
					$query->query_vars['post__in'] = rpt_get_object_relation( $post_id, array() ); 
					// TODO: Manage the case when the post__in field is already charged ?
				}
				
				$query->is_home = false;
				$query->is_archive = true;
				// TODO: Manage rewrite and title ?
			}
		}
	}
}