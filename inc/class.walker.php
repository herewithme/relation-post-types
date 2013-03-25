<?php
/**
 * Create HTML list of relations items.
 *
 * @uses Walker
 */
class Walker_Relations_Menu extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @var string
	 */
	public $tree_type = array( 'post_type' );

	/**
	 * @see Walker::$db_fields
	 * @todo Decouple this.
	 * @var array
	 */
	public $db_fields = array( 'parent' => 'post_parent', 'id' => 'ID' );

	/**
	 * @see Walker::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function start_lvl(&$output, $depth) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<ul class=\"sub-menu\">\n";
	}

	/**
	 * @see Walker::end_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of page. Used for padding.
	 */
	function end_lvl(&$output, $depth) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
	}

	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item item data object.
	 * @param int $depth Depth of item. Used for padding.
	 * @param object $args
	 */
	function start_el(&$output, $item, $depth, $args) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		$output .= $indent . '<li id="item-'. $item->ID . '" class="popular-'.$args->post_type.'">';

		$attributes .= ! empty( $item->url ) ? ' href="'.esc_attr($item->url).'"' : '';

		$item_output  = $args->before;
		$item_output .= '<a'. $attributes .'>';
			$item_output .= $args->link_before . apply_filters( 'the_title', $item->post_title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * @see Walker::end_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Page data object. Not used.
	 * @param int $depth Depth of page. Not Used.
	 */
	function end_el(&$output, $item, $depth) {
		$output .= "</li>\n";
	}
}

/**
 * Create HTML list of relations items.
 *
 * @uses Walker_Relations_Menu
 */
class Walker_Relations_Checklist extends Walker_Relations_Menu  {
	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item item data object.
	 * @param int $depth Depth of item. Used for padding.
	 * @param object $args
	 */
	function start_el(&$output, $item, $depth, $args) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		// Clean possible label
		$item->post_title = trim($item->post_title);
		$item->post_name  = trim($item->post_name);

		$output .= $indent . '<li class="popular-rpt">';
		$output .= '<label class="menu-item-title">';
			$output .= '<input type="checkbox" '.checked( true, in_array($item->ID, (array) $args->current_items), false).' id="in-'.$args->post_type.'-'.$item->ID.'" class="menu-item-checkbox" name="relations[' . $args->post_type . '][]" value="'. esc_attr( $item->ID ) .'" /> ';
			
			if ( !empty($item->post_title) ) {
				$output .= esc_html( $item->post_title );
			} elseif ( !empty($item->post_name) ) {
				$output .= esc_html( $item->post_name );
			} else { 
				$output .= esc_html( sprintf(__('Item %d', 'relations-post-types'), $item->ID) );
			}
		$output .= '</label>';
	}
}