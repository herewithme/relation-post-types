<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'relations-post-types'); ?>:</label>
	<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr($instance['title']); ?>" class="widefat" />
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e('What to show', 'relations-post-types'); ?>:</label>
	<select id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>" class="widefat">
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
	<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>" class="widefat">
		<?php
		foreach( array('post_date' => __('Date', 'relations-post-types'), 'ID' => __('ID', 'relations-post-types'), 'post_title' => __('Title', 'relations-post-types') ) as $optval => $option ) {
			echo '<option '.selected( $instance['orderby'], $optval, false ).' value="'.esc_attr($optval).'">'.esc_html($option).'</option>';
		}
		?>
	</select>
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e("Order by ?", 'relations-post-types'); ?>:</label>
	<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" class="widefat">
		<?php
		foreach( array('ASC' => __('Ascending', 'relations-post-types'), 'DESC' => __('Descending', 'relations-post-types') ) as $optval => $option ) {
			echo '<option '.selected( $instance['order'], $optval, false ).' value="'.esc_attr($optval).'">'.esc_html($option).'</option>';
		}
		?>
	</select>
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e("Number of items to show", 'relations-post-types'); ?>:</label>
	<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo (int) $instance['number']; ?>" class="widefat" />
</p>