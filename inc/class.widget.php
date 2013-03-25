<?php
/**
 * Class for add a new widget for display related custom post type of current item view
 *
 * @package default
 * @author Amaury Balmer
 */
class RelationsPostTypes_Widget extends WP_Widget {
	/**
	 * Constructor
	 *
	 * @return void
	 * @author Amaury Balmer
	 */
	public function __construct() {
		parent::__construct(
			'relation-posttypes', 
			__('Relations Content Widget', 'relations-post-types'), 
			array( 
				'classname' => 'relation-posttypes-widget', 
				'description' => __('Display related content for one or each post type single view.', 'relations-post-types')
			)
		);
	}
	
	/**
	 * Client side widget render
	 *
	 * @param array $args
	 * @param array $instance
	 * @return void
	 * @author Amaury Balmer
	 */
	public function widget( $args, $instance ) {
		global $wp_query;

		extract( $args );
		
		// Singular view ?
		if ( !is_singular() ) {
			return false;
		}
		
		// Post type egal "ALL", delete it !
		if ( $instance['post_type'] == 'all' )  {
			$instance['post_type'] = array();
		}
		
		// Related IDs for this view ?
		$ids = rpt_get_object_relation( $wp_query->get_queried_object_id(), $instance['post_type'] );
		if( $ids == false || empty($ids) ) {
			return false;
		}
		
		// Build or not the name of the widget
		if ( !empty($instance['title']) ) {
			$title = $instance['title'];
		} else {
			$custom_type = get_post_type_object($instance['post_type']);
			$title = $custom_type->labels->name;
		}
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);
		
		$items_query = new WP_Query( array(
			'post__in'			=> $ids,
			'post_type' 		=> $instance['post_type'],
			'post_status' 		=> 'publish',
			'showposts' 	 	=> $instance['number'],
			'orderby' 			=> $instance['orderby'],
			'order' 			=> $instance['order']
		) );
		
		echo $before_widget;
		
		// Display the widget, allow take template from child or parent theme
		if ( is_file(STYLESHEETPATH .'/widget-views/rpt-widget.php') ) { // Use custom template from child theme
			include( STYLESHEETPATH .'/widget-views/rpt-widget.php' );
		} elseif ( is_file(TEMPLATEPATH .'/widget-views/rpt-widget.php' ) ) { // Use custom template from parent theme
			include( TEMPLATEPATH .'/widget-views/rpt-widget.php' );
		} else { // Use builtin temlate
			include( RPT_DIR . 'views/client/widget.php' );
		}

		wp_reset_postdata();
		
		echo $after_widget;
		return true;
	}
	
	/**
	 * Method for save widgets options
	 *
	 * @param string $new_instance
	 * @param string $old_instance
	 * @return void
	 * @author Amaury Balmer
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach ( array('title', 'post_type', 'orderby', 'order', 'number') as $val ) {
			$instance[$val] = strip_tags( $new_instance[$val] );
		}
		return $instance;
	}
	
	/**
	 * Control for widget admin
	 *
	 * @param array $instance
	 * @return void
	 * @author Amaury Balmer
	 */
	public function form( $instance ) {
		$defaults = array(
			'title' 	=> __('Related content', 'relations-post-types'),
			'post_type' => 'all',
			'orderby' 	=> 'post_date',
			'order' 	=> 'DESC',
			'number' 	=> 45
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		include( RPT_DIR . 'views/admin/widget.php' );	
	}
}