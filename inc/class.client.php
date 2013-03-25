<?php
class RelationsPostTypes_Client {
	
	/**
	 * Constructor, register hooks
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	function __construct() {
		// Add query vars, for allow filtering with URL
		add_filter( 'query_vars', array(&$this, 'addQueryVars') );
		add_action( 'parse_query', array(&$this, 'parseQuery') );
		
		// Delete post
		add_action( 'delete_post', array(&$this, 'deletePost') );
	}
	
	/**
	 * Hook call for delete relation of deleted post
	 *
	 * @param integer $post_id 
	 * @return void
	 * @author Amaury Balmer
	 */
	function deletePost( $post_id = 0 ) {
		$type = get_post_type( $post_id );
		rpt_delete_object_relation( (int) $post_id, array($type) );
	}
	
	/**
	 * Add key words relations on search query vars array
	 */
	function addQueryVars( $query_vars = array() ) {
		foreach ( get_post_types( array('show_ui' => true, 'public' => true), 'objects' ) as $post_type ) {
			$query_vars[] = 'rel-'.$post_type->name;
		}
		
		return $query_vars;
	}
	
	/**
	 * Filtering results when rel- key word is set on WP_Query
	 */
	function parseQuery( $query ) {
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