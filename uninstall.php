<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

delete_option( RPT_OPTION );
delete_option( RPT_OPTION.'-settings' );

global $wpdb;
//$wpdb->query("DROP TABLE IF EXISTS {$wpdb->posts_relations}");