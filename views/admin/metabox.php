<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<div id="posttype-<?php echo $post_type_name; ?>" class="categorydiv categorydivrpt">
	<ul id="posttype-<?php echo $post_type_name; ?>-tabs" class="category-tabs">
		<li <?php echo ( 'all' == $current_tab ? ' class="tabs"' : '' ); ?>><a class="nav-tab-link" href="#tabs-panel-posttype-<?php echo $post_type_name; ?>-all"><?php _e( 'View All', 'relation-post-types' ); ?></a></li>
		<li <?php echo ( 'search' == $current_tab ? ' class="tabs"' : '' ); ?>><a class="nav-tab-link" href="#tabs-panel-posttype-<?php echo $post_type_name; ?>-search"><?php _e( 'Search', 'relation-post-types' ); ?></a></li>
	</ul>
	
	<div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-all" class="tabs-panel tabs-panel-view-all <?php echo ( 'all' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' ); ?>">
		<ul id="<?php echo $post_type_name; ?>checklist" class="list:<?php echo $post_type_name; ?> categorychecklist form-no-clear">
			<?php
			$args['walker'] = $walker;
			$checkbox_items = walk_nav_menu_tree( $items_query->posts, 0, (object) $args );
			echo $checkbox_items;
			?>
		</ul>
	</div><!-- /.tabs-all -->

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