<?php

/**
 * Install
 * 
 * @author Stefano Ottolenghi
 * @copyright 2015
 * @package	STI
 */

class STI_install_functions {
    
    /**
     * Walks through available blogs (maybe multisite) and calls the real install procedure
     *
     * @access  public
     * @since   1.0
     * @param   $network_wide bool whether network wide activation has been requested
    */
    
    static function install( $network_wide ) {
        global $wpdb;
        
		//Network activation
        if( $network_wide ) {
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM ".$wpdb->blogs );
			
            foreach( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::install_procedure();
			}
            
			restore_current_blog();
			return;
		}
		
		//Single blog activation
		self::install_procedure();
    }
    
    /**
     * If plugin was activated with a network-wide activation, activate and install it on new blogs when they are created
     *
     * @access  public
     * @since   2.0
     * @param   $blog_id int the id of the newly created blog
     * @param   $user_id int the id of the newly created blog's admin
     * @param   $domain string the domain of the newly created blog's admin
     * @param   $path string the path of the newly created blog's admin
     * @param   $site_id int the site id (usually = 1)
     * @param   $meta array initial site options
    */
    
    static function new_blog_install( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    	if( is_plugin_active_for_network( basename( dirname( dirname( __FILE__ ) ).'/smart-tag-insert.php' ) ) ) {
    		switch_to_blog( $blog_id );
    		self::install_procedure();
    		restore_current_blog();
    	}
    }
    
    /**
     * Adds default settings, current version to the database and assigns default capabilities.
     *
     * @access  public
     * @since   1.0
    */
    
    static function install_procedure() {
        global $sti_global_settings;
        
        $default_settings = array(
            'tags_list' => array(
            	'tag1',
            	'tag2',
				"this a sample tag",
				"either a single word or with spaces"
			),
        	'allowed_post_types' => array(
        		'post'
        	),
			'user_roles_allowed_to_add_tags' => array(
				'administrator',
				'editor'
			),
        	'default_tags_number' => 3
		);
        
		//Only add default settings if not there already
		$current_settings = get_option( $sti_global_settings['option_name'] );
        
        if( ! is_array( $current_settings ) )
			update_option( $sti_global_settings['option_name'], $default_settings );
        
        update_option( 'sti_current_version', $sti_global_settings['newest_version'] );
    }
}
?>