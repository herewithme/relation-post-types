<?php
/**
 * Display the first post's relations, allow to specify the want values to display.
 *
 * @param int $post_id Post ID.
 * @param string|array $post_type Post type name.
 * @param string $field Field to get
 * @return string|object|integer|false
 */
function the_first_relation( $post_id = null, $post_type = 'post', $field = 'id' ) {
	if ( $field == 'object' ) {
		return false;
	}
	
	echo get_first_relation( $post_id , $post_type, $field );
}

/**
 * Retrieve the first post's relations, allow to specify the want values to return.
 *
 * @param int $post_id Post ID.
 * @param string|array $post_type Post type name.
 * @param string $field Field to get
 * @return string|object|integer|false
 */
function get_first_relation( $post_id = null, $post_type = 'post', $field = 'id' ) {
	if ( (int) $post_id == 0 ) {
		global $post;
		$post_id = $post->ID;
	}
	
	$relations = rpt_get_object_relation( $post_id, array($post_type), true );
	if ( empty($relations) ) {
		return false;
	}
	
	switch ( $field ) {
		case 'link':
			return get_permalink($relations);
			break;
		case 'object':
			return get_post($relations);
			break;
		case 'name':
			return get_the_title($relations);
			break;
		default:
		case 'id':
			return (int) $relations;
			break;
	}
}

/**
 * Retrieve a post's relations as a list with specified format.
 *
 * @param int $id Post ID.
 * @param string|array $post_type Post type name.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 * @return string
 */
function get_the_relations( $id = 0, $post_type, $before = '', $sep = '', $after = '' ) {
	$relations = rpt_get_object_relation( $id, $post_type, false );
	if ( is_wp_error( $relations ) ) {
		return $relations;
	}
	
	if ( empty( $relations ) ) {
		return false;
	}
	
	foreach ( $relations as $relation ) {
		$link = get_permalink( $relation );
		if ( is_wp_error( $link ) ) {
			return $link;
		}

		$relation_links[] = '<a href="' . $link . '" title="' . esc_attr(get_the_title($relation)) . '">' . get_the_title($relation) . '</a>';
	}

	$relation_links = apply_filters( "get_the_relations", $relation_links, $post_type );

	return $before . join( $sep, $relation_links ) . $after;
}

/**
 * Display the relations in a list.
 *
 * @param int $id Post ID.
 * @param string|array $post_type Post type name.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 * @return null|bool False on WordPress error. Returns null when displaying.
 */
function the_relations( $id = 0, $post_type, $before = '', $sep = ', ', $after = '' ) {
	$relation_list = get_the_relations( $id, $post_type, $before, $sep, $after );

	if ( is_wp_error( $relation_list ) ) {
		return false;
	}

	echo apply_filters('the_relations', $relation_list, $post_type, $before, $sep, $after);
}