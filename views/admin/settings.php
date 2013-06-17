<?php
// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php _e("Relations post types : Settings", 'relations-post-types'); ?></h2>
	
	<div class="message updated">
		<p><?php _e('<strong>Warning :</strong> Check or uncheck relations between 2 post types will not delete relations on DB.', 'relations-post-types'); ?></p>
	</div>

	<form action="" method="post">
		<div id="col-container">
			<h3><?php _e("Relations between post types", 'relations-post-types'); ?></h3>
			<p><?php _e('Instructions for use: lines correspond to each page of edition of the post type, you can show the box of relations with others contents by checking the columns of your choice', 'relations-post-types'); ?></p>
			
			<table class="widefat tag fixed" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" id="label" class="manage-column column-name"><?php _e('Custom types', 'relations-post-types'); ?></th>
						<?php
						foreach ( get_post_types( array(), 'objects' ) as $post_type ) {
							if ( !$post_type->show_ui || empty($post_type->labels->name) )
								continue;
							
							echo '<th scope="col">'.esc_html($post_type->labels->name).'</th>';
						}
						?>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th scope="col" class="manage-column column-name"><?php _e('Custom types', 'relations-post-types'); ?></th>
						<?php
						foreach ( get_post_types( array(), 'objects' ) as $post_type ) {
							if ( !$post_type->show_ui || empty($post_type->labels->name) )
								continue;
							
							echo '<th scope="col">'.esc_html($post_type->labels->name).'</th>';
						}
						?>
					</tr>
				</tfoot>
	
				<tbody id="the-list" class="list:relations">
					<?php
					$class = 'alternate';
					$i = 0;
					foreach ( get_post_types( array(), 'objects' ) as $post_type ) :
						if ( !$post_type->show_ui || empty($post_type->labels->name) )
							continue;
						
						$i++;
						$class = ( $class == 'alternate' ) ? '' : 'alternate';
						?>
						<tr id="custom type-<?php echo $i; ?>" class="<?php echo $class; ?>">
							<th class="name column-name"><?php echo esc_html($post_type->labels->name); ?></th>
							<?php
							foreach ( get_post_types( array(), 'objects' ) as $line_post_type ) {
								if ( !$line_post_type->show_ui || empty($line_post_type->labels->name) )
									continue;

								echo '<td>' . "\n";
									//if ( $line_post_type->name != $post_type->name ) {
										if ( !isset($current_relations[$line_post_type->name]) )
											$current_relations[$line_post_type->name] = array();
											
										echo '<input type="checkbox" name="rpt_relations['.$line_post_type->name.']['.$post_type->name.']" value="1" '.checked( true, in_array( $post_type->name, (array) $current_relations[$line_post_type->name] ), false ).' />' . "\n";
									//} else {
									//	echo '-' . "\n";
									//}
								echo '</td>' . "\n";
							}
							?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h3><?php _e("Items quantity on metabox", 'relations-post-types'); ?></h3>
			<p class="description"><?php _e('If your writing page is very slow, you should probably reduce the number of elements. Enter zero for no limit.', 'relations-post-types'); ?></p>
			<table class="form-table">
				<?php
				foreach ( get_post_types( array(), 'objects' ) as $post_type ) :
					if ( !$post_type->show_ui || empty($post_type->labels->name) ) {
						continue;
					}

					$qty_value = ( isset($current_settings['quantity'][$post_type->name]) ) ? (int) $current_settings['quantity'][$post_type->name] : 0;
					?>
					<tr valign="top">
						<th scope="row"><label for="rpt-quantity"><?php echo esc_html($post_type->labels->name); ?></label></th>
						<td>
							<input name="rpt_settings[quantity][<?php echo esc_attr($post_type->name); ?>]" type="number" id="rpt-quantity" value="<?php echo esc_attr($qty_value); ?>" class="small-text" />
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		
			<p class="submit">
				<?php wp_nonce_field( 'save-relations-settings' ); ?>
				<input class="button-primary" name="save-relations" type="submit" value="<?php _e('Save settings', 'relations-post-types'); ?>" />
			</p>
		</form>
	</div><!-- /col-container -->
</div>

<div class="wrap">
	<h2><?php _e("Relations post types : Export/Import", 'relations-post-types'); ?></h2>
	
	<a class="button" href="<?php echo wp_nonce_url(self::$admin_url.'&amp;action=export_config_rpt', 'export-config-rpt'); ?>"><?php _e("Export config file", 'relations-post-types'); ?></a>
	<a class="button" href="#" id="toggle-import_form"><?php _e("Import config file", 'relations-post-types'); ?></a>
	<script type="text/javascript">
		jQuery("#toggle-import_form").click(function(event) {
			event.preventDefault();
			jQuery('#import_form').removeClass('hide-if-js');
		});
	</script>
	<div id="import_form" class="hide-if-js">
		<form action="<?php echo self::$admin_url ; ?>" method="post" enctype="multipart/form-data">
			<p>
				<label><?php _e("Config file", 'relations-post-types'); ?></label>
				<input type="file" name="config_file" />
			</p>
			<p class="submit">
				<?php wp_nonce_field( 'import_config_file_rpt' ); ?>
				<input class="button-primary" type="submit" name="import_config_file_rpt" value="<?php _e('I want import a config from a previous backup, this action will REPLACE current configuration', 'relations-post-types'); ?>" />
			</p>
		</form>
	</div>
</div>