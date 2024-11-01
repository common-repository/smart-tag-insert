<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2013
 * @package	STI
 */

class AT_save_options {
    
    /**
     * Saves options 
     *
     * @access  public
     * @since   1.0
     * @param   $settings array new settings
    */
    
    static function save_settings( $settings ) {
    	global $sti_global_settings;
    	
    	$new_settings = array( 
    			'allowed_post_types' => array(), 
    			'user_roles_allowed_to_add_tags' => array() 
    	);
    	
    	$new_settings['tags_list'] = explode( "\r\n", trim( $settings['tags_list'] ) );
    	
    	$also_categories = "";
    	foreach( $new_settings['tags_list'] as $single ) {
    		if( term_exists( $single, 'category' ) != 0 ) $also_categories .= $single."<br />";
    	}
    	if( strlen( $also_categories ) > 0 ) 
    		$also_categories = new WP_Error( 'sti_tags_also_categories', sprintf( __( "Warning: settings have been successfully updated, but some of the tags in your list are categories as well, and you may want to delete them from the tags list:%s", 'smart-tag-insert' ), '<br /><br />'.$also_categories ) );
    	
    	$new_settings['default_tags_number'] = (int) trim( $settings['default_tags_number'] );
           
    	foreach( $settings as $option => $value ) {
    	
    		//If option is a checkbox/radio already dealt with, skip it
    		if( $value === NULL )
    			continue;
    	
    		if( strpos( $option, 'post_type_' ) === 0 ) {
    			$new_settings['allowed_post_types'][] = $value;
    			continue;
    		}
    		
    		if( strpos( $option, 'user_role_tag' ) === 0 ) {
    			$new_settings['user_roles_allowed_to_add_tags'][$value] = $value;
    			continue;
    		}
    	}
    	
        $new_settings = apply_filters( 'sti_save_settings', $new_settings, $settings );
        $new = array_merge( $sti_global_settings['plugin_settings'], $new_settings );
        
        $update = self::update_settings( $new );
        if( is_wp_error( $update ) ) return $update;
        if( is_wp_error( $also_categories ) ) return $also_categories; //warning
    }
    
    /**
     * Stores the new settings in the database, also update global var 
     *
     * @access  public
     * @since   1.0
     * @param   $settings array the new settings
    */
    
    static function update_settings( $settings ) {
        global $sti_global_settings;
		
        if( $settings == $sti_global_settings['plugin_settings'] ) return; //avoid updating with same data, which would result in an error
        
        if( ! $update = update_option( $sti_global_settings['option_name'], $settings ) )
            return new WP_Error( 'sti_save_general_settings_error', __( 'Error: could not update settings.' , 'smart-tag-insert' ) );
        
        $sti_global_settings['plugin_settings'] = $settings;
        
        do_action( 'sti_settings_updated' );
    }
}
?>