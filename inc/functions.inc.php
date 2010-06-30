<?php
/**
 * This method allow to link a custom post type by passing his ID, the second param allow to put one or lot's of objects ID...
 *
 * @param integer $custom_id 
 * @param array $object_ids 
 * @param boolean $append (Optionnal)
 * @return void
 * @author Amaury Balmer
 */
function rpt_set_object_relation( $custom_id = 0, $object_ids = array(), $append = true ) {
	global $wpdb;
	
	// Object ID is valid ?
	$custom_id = (int) $custom_id;
	if ( $custom_id == 0 ) {
		return false;
	}
	
	// No append ? replace ! delete before !
	if ( $append == false ) {
		rpt_delete_object_relation( $custom_id, array() );
	}
	
	// Always an array ?
	if ( !is_array($object_ids) )
		$object_ids = array( (int) $object_ids );
		
	// Cast values and make unique
	$object_ids = array_map( 'intval', $object_ids );
	$object_ids = array_unique( $object_ids );
	
	// No valid ID ?
	if ( empty($object_ids) )
		return false;
		
	// Loop for insert on DB !
	foreach( (array) $object_ids as $object_id ) {
		if ( $object_id == 0 || $object_id == $custom_id ) continue; // No zero, no master/master !
		
		$wpdb->insert( $wpdb->relations, array( 'object_id_1' => $custom_id, 'object_id_2' => $object_id ) );
	}
	
	return true;
}

/**
 * This function allow to get for a specific post ID, all object ID of one or many post type
 *
 * @param integer $custom_id 
 * @param array $post_types
 * @return void
 * @author Amaury Balmer
 */
function rpt_get_object_relation( $custom_id = 0, $post_types = array() ) {
	global $wpdb;
	
	// Object ID is valid ?
	$custom_id = (int) $custom_id;
	if ( $custom_id == 0 ) {
		return false;
	}
	
	// Always an array for post type ?
	if ( is_string($post_types) && !empty($post_types) )
		$post_types = array( $post_types );
	
	$restrict_posts = '';
	if ( !empty($post_types) ) {
		$ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type IN ('".implode("', '",$post_types)."')");
		if ( $ids == false )
			return false;
		$restrict_posts = " AND (object_id_1 IN (".implode(',',$ids).") OR object_id_2 IN (".implode(',',$ids)."))";
	}
		
	// Make query to get relation ID depending the post types !
 	$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->relations WHERE (object_id_1 = %d OR object_id_2 = %d) $restrict_posts", $custom_id, $custom_id));

	// Clean array for return... only take the right ID...
	$post_ids = array();
	foreach( (array) $results as $result ) {
		if ( $result->object_id_1 == $custom_id ) {
			$post_ids[] = $result->object_id_2;
		} else { // object_id_2
			$post_ids[] = $result->object_id_1;
		}
	}
	
	return $post_ids;
}

/**
 * This method allow to delete relations
 *
 * @param string $custom_id
 * @param array $post_types
 * @return void
 * @author Amaury Balmer
 */
function rpt_delete_object_relation( $custom_id = 0, $post_types = array() ) {
	global $wpdb;
	
	$custom_id = (int) $custom_id;
	if ( $custom_id == 0 ) 
		return false;
		
	// Always an array for post type ?
	if ( is_string($post_types) && !empty($post_types) )
		$post_types = array( $post_types );
	elseif ( empty($post_types) ) {
		$post_types = array( get_post_type($custom_id) );
	}
		
	$restrict_posts = '';
	if ( !empty($post_types) ) {
		$ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type IN ('".implode("', '",$post_types)."')");
		if ( $ids == false )
			return false;
		$restrict_posts = " AND (object_id_1 IN (".implode(',',$ids).") OR object_id_2 IN (".implode(',',$ids)."))";
	}
		
	return $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->relations WHERE object_id_1 = %d OR object_id_2 = %d $restrict_posts", $custom_id, $custom_id) );
}
?>