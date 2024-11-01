<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2015
 * @package	STI
 */

//Uninstall must have been triggered by WordPress
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit;

global $wpdb;

function sti_uninstall_procedure() {
    if( get_option( 'sti_current_version' ) )
        delete_option( 'sti_current_version' );
    
    if( get_option( 'sti_settings' ) )
        delete_option( 'sti_settings' );
}

//If working on a multisite blog, get all blog ids, foreach them and call the uninstall procedure on each of them
if( function_exists( 'is_multisite' ) AND is_multisite() ) {
    global $wpdb;
	
	$blog_ids = $wpdb->get_col( 'SELECT blog_id FROM '.$wpdb->blogs );
    foreach( $blog_ids as $blog_id ) {
		switch_to_blog( $blog_id );
        sti_uninstall_procedure();
	}
    
	restore_current_blog();
	return;
}

sti_uninstall_procedure();
?>