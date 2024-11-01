<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2015
 * @package STI
 */

class STI_update_class {
    
    /**
     * Walks through available blogs (maybe multisite) and calls the update procedure
     *
     * @access  public
     * @since   1.0
    */
    
    static function update() {
        global $wpdb;
        
        if ( ! function_exists( 'is_plugin_active_for_network' ) )
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        
		if( is_plugin_active_for_network( basename( dirname( dirname( __FILE__ ) ).'/smart-tag-insert.php' ) ) ) {
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM ".$wpdb->blogs );
			
            foreach( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::update_exec();
			}
            
			restore_current_blog();
			return;
		}
        
    	self::update_exec();
    }
    
    /**
     * Runs update procedure.
     * 
     * Also updates current version option and pages permissions.
     *
     * @access  public
     * @since   1.0
    */
    
    static function update_exec() {
        global $sti_global_settings;
        
        $plugin_settings = $sti_global_settings['plugin_settings'];
        $plugin_settings_old = $plugin_settings;
		
        /**
         * Settings updates
         */
        
        $new_settings = array(  
        );
        
        foreach( $new_settings as $setting => $value ) {
            if( ! isset( $plugin_settings[$setting] ) )
                $plugin_settings[$setting] = $value;
        }
		
        if( $plugin_settings != $plugin_settings_old ) {
            if( ! update_option( $sti_global_settings['option_name'], $plugin_settings ) ) {
                $error = new WP_Error( 'sti_update_settings_error', __( 'Error: could not update settings.', 'smart-tag-insert' ), array(
                    'settings' => $plugin_settings,
                    'settings_start' => $plugin_settings_old
                ) );
                return $error;
            }
		}
    		
        update_option( 'sti_current_version', $sti_global_settings['newest_version'] );
    }
}

?>