<?php
/* 
Author of Sample :
	Mateus Reis
	mateus@laboratorio72.com
	skype: mateusreis
*/

$artists_relation = rpt_get_object_relation($id_post, 'artista');
if ( count($artists_relation) >= 1 ) {
	global $post;

	$args = array(
		'post_type' => 'artista',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'showposts' => -1,
		'post__in' => $artists_relation,
		'orderby' => 'date',
		'order' => 'DESC',
	);

	$results_artists = query_posts($args);
	
	echo '<ul>' . "\n";
	foreach ( $results_artists as $post ) {
		// stuff
		echo '<li><a href="'.get_permalink($post).'">'.get_the_title().'</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
}
?>