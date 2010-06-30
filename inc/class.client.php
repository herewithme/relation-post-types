<?php
class RelationPostTypes_Client {
	/**
	 * Constructor, register hooks
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	function RelationPostTypes_Client() {
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
}
?>