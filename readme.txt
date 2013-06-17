=== Relations Post Types ===

Contributors: momo360modena,Rahe
Donate link: http://www.beapi.fr/donate/
Tags: custom, post types, cms, post type, relation, connections, custom post types, many-to-many, relationships
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

This plugin allow to build relation between 2 custom types (posts, page, custom), very useful for manage related content on CMS type website.

A few example use cases:

 * manually lists of related posts
 * post series
 * rented houses connected to agency
 * etc.

Relationships are created from a settings page from the administration console.

This plugin can not do relationships with users, you should test this excellent plugin made by scribu
http://wordpress.org/extend/plugins/posts-to-posts/

== Installation ==

1. Download, unzip and upload to your WordPress plugins directory
2. Activate the plugin within you WordPress Administration Backend
3. Go to Settings > Relations and enable relation between post type.

= Display relations in its theme =

Example, display 5 related pages in your single.php template :

`
<?php
$related_pages_ids = rpt_get_object_relation($id_post, 'page');
if ( count($related_pages_ids) >= 1 ) {
	$related_pages = query_posts( array(
		'post_type' => 'page',
		'post_status' => 'publish',
		'posts_per_page' => 5
		'post__in' => $related_pages_ids,
		'orderby' => 'post_date',
		'order' => 'DESC',
	) );

	echo 'Related pages' . "\n";
	echo '<ul>' . "\n";
	foreach ( $related_pages as $post ) {
		echo '<li><a href="'.get_permalink($post).'">'.get_the_title($post).'</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
}
?>
`

== Screenshots ==

1. Metabox in Post Types for creating a relation
2. Option page to set relations between two content types

== Changelog ==

* Version 1.3.1 :
	* Change settings for quantity, allow to set quantity for each post type
	* Add DIE for direct access
	* Add UNINSTALL method, remove only option. (not yet DB) 
	* Move translation to INIT hook
* Version 1.3 : 
	* Compatibility with WP 3.5
	* Refactory code: use static methods, use views, new conding standards
	* Add setting for set quantity items
	* Improve performance for rpt_get_objects_most_used()
* Version 1.2.4 : 
	* Compatibility with WP 3.4
* Version 1.2.3 :
	* No released version
* Version 1.2.2 : 
	* Fix a potential error with PHP Opcode Cache
	* Remove most recent tab, performance bad
* Version 1.2.1 :
	* Add query var with prefix "rel-" for each CPT allow filtering on URL
* Version 1.2 :
	* Stable enough for remove beta version
	* Add some template functions for an easier usage
	* Fix a very rare bug when a post type have relation with the same post type and when this relation is empty, that delete others relations of this post id. (thanks to bniess for reporting bug)
* Version 1.1-beta2 :
	* Fix possible bug with others plugin's beapi that use import 
* Version 1.1-beta1 :
	* Add tab for searching
	* Add tab to select most used elements
	* Add function to get the most associated elements from a post_type
	* Add import/export config tool
* Version 1.0.13 :
	* Fix a warning on admin write page
* Version 1.0.12 :
	* Fix a bug with CSS not existing. (bug copy/paste)
	* Add a argument for get single relation
* Version 1.0.11 :
	* Fix name plugin
	* Possible fix for widget, add reset and use "post__in" instead "include"
* Version 1.0.10 :
	* Fix bug with data lost and quick edit
* Version 1.0.9 :
	* Fix possible bug with folder name
	* Fix bug with functions API and switch_to_blog()
* Version 1.0.8 :
	* Fix potential warning during saving.
	* Fix bug that not allow to uncheck all relations
* Version 1.0.7 :
	* Fix several bugS during saving relations, get relations.
* Version 1.0.6 :
	* Check compatibility with WP 3.0.1
	* Fix constant construction. (try dynamic method)
* Version 1.0.5 :
	* Fix renaming class
* Version 1.0.4 :
	* Fix generation of metabox on admin. Fix for correspond to description and remove stupid return...
* Version 1.0.3 :
	* Fix notices
	* Allow relations between objects of the same post type
* Version 1.0.2 :
	* Optimize some PHP codes
	* Add POT language + French
* Version 1.0.1 :
	* Finalize widget !
	* Add nonce for improve security
* Version 1.0.0 :
	* First version stable