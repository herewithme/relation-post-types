<?php
/**
 * This method allow to link a custom post type by passing his ID, the second param allow to put one or lot's of objects ID...
 *
 * @param integer $custom_id 
 * @param array $object_ids 
 * @param array $post_types 
 * @param boolean $append (Optionnal)
 * @return void
 * @author Amaury Balmer
 */
function rpt_set_object_relation( $custom_id = 0, $object_ids = array(), $post_types = array(), $append = true ) {
	global $wpdb;
	
	// Object ID is valid ?
	$custom_id = (int) $custom_id;
	if ( $custom_id == 0 ) {
		return false;
	}
	
	// No append ? replace ! delete before !
	if ( $append == false ) {
		rpt_delete_object_relation( $custom_id, $post_types );
	}
	
	// Always an array ?
	if ( !is_array($object_ids) ) {
		$object_ids = array( (int) $object_ids );
	}

	// Cast values and make unique
	$object_ids = array_map( 'intval', $object_ids );
	$object_ids = array_unique( $object_ids );
	
	// No valid ID ?
	if ( empty($object_ids) ) {
		return false;
	}
		
	// Loop for insert on DB !
	foreach( (array) $object_ids as $object_id ) {
		if ( $object_id == 0 || $object_id == $custom_id ) {
			continue; // No zero, no master/master !
		}
		
		$wpdb->insert( $wpdb->posts_relations, array( 'object_id_1' => $custom_id, 'object_id_2' => $object_id ) );
	}
	
	return true;
}

/**
 * This function allow to get for a specific post ID, all object ID of one or many post type
 *
 * @param integer $custom_id 
 * @param array $post_types
 * @param boolean $single
 * @return array|false|integer
 * @author Amaury Balmer
 */
function rpt_get_object_relation( $custom_id = 0, $post_types = array(), $single = false ) {
	global $wpdb;
	
	// Object ID is valid ?
	$custom_id = (int) $custom_id;
	if ( $custom_id == 0 ) {
		return false;
	}
	
	// Always an array for post type ?
	if ( is_string($post_types) && !empty($post_types) ) {
		$post_types = array( $post_types );
	}
	
	$restrict_posts = '';
	if ( !empty($post_types) ) {
		$ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE ID != %d AND post_type IN ('".implode("', '", $post_types)."')", $custom_id));
		if ( $ids == false ) {
			return false;
		}
		$restrict_posts = " AND (object_id_1 IN (".implode(',',$ids).") OR object_id_2 IN (".implode(',',$ids)."))";
	}
		
	// Make query to get relation ID depending the post types !
 	$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts_relations WHERE (object_id_1 = %d OR object_id_2 = %d) $restrict_posts", $custom_id, $custom_id));
 	if ( $results == false || empty($results) ) {
		return false;
	}
	
	// Clean array for return... only take the right ID...
	$post_ids = array();
	foreach( (array) $results as $result ) {
		if ( $result->object_id_1 == $custom_id ) {
			$post_ids[] = (int) $result->object_id_2;
		} else { // object_id_2
			$post_ids[] = (int) $result->object_id_1;
		}
	}
	
	if ( $single == true && is_array($post_ids) && !empty($post_ids) ) {
		return current($post_ids);
	} elseif( $single == true  ){
		return false;
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
	if ( $custom_id == 0 ) {
		return false;
	}
		
	// Always an array for post type ?
	if ( is_string($post_types) && !empty($post_types) )
		$post_types = array( $post_types );
	elseif ( empty($post_types) ) {
		$post_types = array( get_post_type($custom_id) );
	}
	
	$restrict_posts = '';
	if ( !empty($post_types) ) {
		$ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type IN ('".implode("', '",$post_types)."') AND ID <> ".$custom_id);
		if ( $ids == false ) {
			return false;
		}
		
		$restrict_posts = " AND (object_id_1 IN (".implode(',',$ids).") OR object_id_2 IN (".implode(',',$ids)."))";
	}

	return $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->posts_relations WHERE (object_id_1 = %d OR object_id_2 = %d) $restrict_posts", $custom_id, $custom_id) );
}

/**
 * This method allow to get items of one content type which have relation with a another post type... The first arg is the post type use for return IDs...
 *
 * @param string $return_post_type 
 * @param string $comparaison_post_type 
 * @return array|boolean
 * @author Amaury Balmer
 */
function rpt_get_objects_with_relations( $return_post_type = '', $comparaison_post_type = '' ) {
	global $wpdb;
	
	// Get IDs for both post type
 	$ids1 = $wpdb->get_col( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s", $return_post_type) );
	$ids2 = $wpdb->get_col( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s", $comparaison_post_type) );
	
	// Build SQL Where
	$where = '';
	foreach( $ids1 as $id1 ) {
		foreach( $ids2 as $id2 ) {
			$where .= " OR ( (object_id_1 = $id1 AND object_id_2 = $id2) OR (object_id_1 = $id2 AND object_id_2 = $id1) ) ";
		}
	}
	
	// Get ID of relations
	$results = $wpdb->get_results("SELECT DISTINCT * 
		FROM $wpdb->posts_relations WHERE (
			object_id_1 IN (".implode(',', $ids1).") AND object_id_2 IN (".implode(',', $ids2).")
		)
		OR (
			object_id_1 IN (".implode(',', $ids2).") AND object_id_2 IN (".implode(',', $ids1).")
		)");
	
	// Clean array for return... only take the ID in right post type...
	$post_ids = array();
	foreach( (array) $results as $result ) {
		if ( in_array($result->object_id_1, $ids1) ) {
			$post_ids[] = $result->object_id_1;
		} elseif ( in_array($result->object_id_2, $ids1) ) {
			$post_ids[] = $result->object_id_2;
		}
	}
	
	return $post_ids;
}

/**
 * Function for get most used relation for a post type.
 *
 */
function rpt_get_objects_most_used( $return_post_type = '' ) {
	global $wpdb;
	
	// Get IDs for both post type
	$ids = $wpdb->get_col( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s", $return_post_type) );	
	if ( $ids == false ) {
		return false;
	}
	
	// Build SQL Where
	$where = " object_id_1 IN (".implode(',', $ids).") OR object_id_2 IN (".implode(',', $ids).")";
		
	// Get ID of relations
	$results = $wpdb->get_col("
		SELECT post_id, count
		FROM (
			(SELECT object_id_1 AS post_id, count(object_id_1) AS count
				FROM $wpdb->posts_relations
				WHERE $where 
				GROUP BY object_id_1)
			UNION ALL 
			(SELECT object_id_2 AS post_id, count(object_id_2) AS count
				FROM $wpdb->posts_relations
				WHERE $where 
				GROUP BY object_id_2)
		) AS tables
		GROUP BY post_id 
		ORDER BY count DESC");

	return $results;
}