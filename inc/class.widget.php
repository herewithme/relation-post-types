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
	function RelationsPostTypes_Widget() {
		$this->WP_Widget( 'relation-posttypes', __('Relations Content Widget', 'relations-post-types'), array( 'classname' => 'relation-posttypes-widget', 'description' => __('Display related content for one or each post type single view.', 'relations-post-types') ) );
	}
	
	/**
	 * Client side widget render
	 *
	 * @param array $args
	 * @param array $instance
	 * @return void
	 * @author Amaury Balmer
	 */
	function widget( $args, $instance ) {
		global $wp_query;
		extract( $args );
		
		// Singular view ?
		if ( !is_singular() )
			return false;
		
		// Post type egal "ALL", delete it !
		if ( $instance['post_type'] == 'all' )
			$instance['post_type'] = array();
		
		// Related IDs for this view ?
		$ids = rpt_get_object_relation( $wp_query->get_queried_object_id(), $instance['post_type'] );
		if( $ids == false || empty($ids) )
			return false;
		
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
		if ( isset($title) )
			echo $before_title . $title . $after_title;
		
		if ( !$items_query->have_posts()  ) {
			echo '<p class="no-result">'.__('No items actually for this custom type.', 'relations-post-types').'</p>';
		} else {
			echo '<ul class="relations-list">' . "\n";
			
			while ($items_query->have_posts()) : $items_query->the_post();
				?>
				<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></li>
				<?php
			endwhile;
			
			echo '</ul>' . "\n";
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
	function update( $new_instance, $old_instance ) {
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
	function form( $instance ) {
		$defaults = array(
			'title' 	=> __('Related content', 'relations-post-types'),
			'post_type' => 'all',
			'orderby' 	=> 'post_date',
			'order' 	=> 'DESC',
			'number' 	=> 45
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'relations-post-types'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e('What to show', 'relations-post-types'); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>" style="width:100%;">
				<option value="all"><?php _e('All', 'relations-post-types'); ?></option>
				<?php
				foreach ( get_post_types( array(), 'objects' ) as $post_type ) {
					if ( !$post_type->show_ui || empty($post_type->labels->name) )
						continue;
					
					echo '<option '.selected( $instance['post_type'], $post_type->name, false ).' value="'.esc_attr($post_type->name).'">'.esc_html($post_type->labels->name).'</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e("Order on which field ?", 'relations-post-types'); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>" style="width:100%;">
				<?php
				foreach( array('post_date' => __('Date', 'relations-post-types'), 'ID' => __('ID', 'relations-post-types'), 'post_title' => __('Title', 'relations-post-types') ) as $optval => $option ) {
					echo '<option '.selected( $instance['orderby'], $optval, false ).' value="'.esc_attr($optval).'">'.esc_html($option).'</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e("Order by ?", 'relations-post-types'); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" style="width:100%;">
				<?php
				foreach( array('ASC' => __('Ascending', 'relations-post-types'), 'DESC' => __('Descending', 'relations-post-types') ) as $optval => $option ) {
					echo '<option '.selected( $instance['order'], $optval, false ).' value="'.esc_attr($optval).'">'.esc_html($option).'</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e("Number of items to show", 'relations-post-types'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo (int) $instance['number']; ?>" style="width:100%;" />
		</p>
	<?php
	}
}
?>