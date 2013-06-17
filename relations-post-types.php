<?php
/*
Plugin Name: Relation Post Types
Plugin URI: http://wordpress.org/extend/plugins/relation-post-types/
Description: Allow to build relations between 2 custom types.
Author: Amaury Balmer, Nicolas Juen
Author URI: http://www.beapi.fr
Version: 1.3.1
Text Domain: relations-post-types
Domain Path: /languages/
Network: false

----

Copyright 2012 Amaury Balmer (amaury@beapi.fr)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

---

TODO :
	Allow registration of relation with an API code
	Allow to export DB options to php files
	Allow to hide options page with CONSTANT
	Allow multiple selections (checkbox current) / Allow unique selection (select or autocompletion)
	Allow metabox "Relations" that display relations for each contents.
	Allow to merge relation into ONE metabox
	Add an parameter to WP_Query for easier request
	Add a shortcode for display related content
*/

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

// Setup table name for relations
global $wpdb;
$wpdb->tables[] 		= 'posts_relations';
$wpdb->posts_relations 	= $wpdb->prefix . 'posts_relations';

// Folder name
define ( 'RPT_VERSION', '1.3.1' );
define ( 'RPT_OPTION',  'relations-post-types' );

define('RPT_URL', plugin_dir_url ( __FILE__ ));
define('RPT_DIR', plugin_dir_path( __FILE__ ));

// Library
require( RPT_DIR . 'inc/functions.inc.php' );
require( RPT_DIR . 'inc/functions.tpl.php' );

// Call client class and functions
require( RPT_DIR . 'inc/class.base.php' );
require( RPT_DIR . 'inc/class.walker.php' );
require( RPT_DIR . 'inc/class.client.php' );
require( RPT_DIR . 'inc/class.widget.php' );

if ( is_admin() ) { // Call admin class
	require( RPT_DIR . 'inc/class.admin.php' );
	require( RPT_DIR . 'inc/class.admin.post.php' );
}

// Activate/Desactive Relation Post Types
register_activation_hook  ( __FILE__, array('RelationsPostTypes_Base', 'activate') );
// register_deactivation_hook( __FILE__, array('RelationsPostTypes_Base', 'deactivate') );

add_action( 'plugins_loaded', 'init_relations_post_types' );
function init_relations_post_types() {
	// Client
	new RelationsPostTypes_Client();
	
	// Admin
	if ( is_admin() ) {
		new RelationsPostTypes_Admin();
		new RelationsPostTypes_Admin_Post();
	}
	
	// Widget
	add_action( 'widgets_init', create_function('', 'return register_widget("RelationsPostTypes_Widget");') );
}