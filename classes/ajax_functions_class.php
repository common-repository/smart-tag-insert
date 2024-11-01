<?php

require_once( 'save_options_class.php' );

/**
 * AJAX functions handler.
 * 
 * @package		STI
 * @since 		1.0
 * @author 		Stefano Ottolenghi
 * @copyright 	2015
 */

class STI_ajax_functions {
    
    /**
     * Checks whether the AJAX request is legitimate, if not displays an error that the requesting JS will display.
     *
     * @access  public
     * @since   1.0
     * @param   $nonce string the WP nonce  
    */
    
    static function sti_check_ajax_referer( $nonce ) {
        if( ! check_ajax_referer( $nonce, false, false ) )
            die( __( 'Error: Seems like AJAX request was not recognised as coming from the right page. Maybe hacking around..?' , 'smart-tag-insert') );
    }
    
    /**
     * Saves settings
     *
     * @access  public
     * @since   1.0
     */
    
    static function save_settings() {
        self::sti_check_ajax_referer( 'sti_save_settings' );
        parse_str( $_REQUEST['form_data'], $settings );
        
        $save_settings = AT_save_options::save_settings( $settings );
        if( is_wp_error( $save_settings ) ) die( $save_settings->get_error_message() );
    }
    
    /**
     * Handles the AJAX request for loading tags related to a post.
     *
     * @access  public
     * @since   1.0
     */
    
    static function load_tags() {
    	global $sti_global_settings;
    	self::sti_check_ajax_referer( 'sti_load_tags' );
    
    	$post = get_post( $_REQUEST["post_id"] );
    	$tags_relevances = STI_general_functions::get_tags_relevance( $sti_global_settings['plugin_settings']['tags_list'], $post->post_content );
    	
    	$output = "<form id=\"sti_tags_selection\">
<table class=\"widefat fixed\">
	<thead>
    	<tr>
    		
    		<th width=\"78%\">".__( "Tag", "at" )."</th>
    		<th>".__( "Rel.", "at" )."</th>
    	</tr>
    </thead>
    <tbody>";
    	
    	$n = 0;
    	foreach( $tags_relevances as $tag => $relevance ) {
    		if( $relevance == 0 ) break; //the list is sorted
    		if( $n > 9 ) break; //display first 10 elements
    		
    		$checked = "";
    		if( $n < $sti_global_settings['plugin_settings']['default_tags_number'] ) $checked = " checked = \"checked\""; // check first 3
    		++$n;
    		
    		$output .= "<tr>
    		<td><label><input type=\"checkbox\" name=\"sti_tag-".$tag."\" value=\"".$tag."\"".$checked.">
    		".$tag."</label></td>
    		<td>".round( $relevance, 0 )."%</td>
    		</tr>";
    	}
    	
    $output .= "</tbody>
</table>
</form>";
    
    die( $output );
    
    }
    
    /**
     * Handles the AJAX request for adding selected tags to a post.
     *
     * @access  public
     * @since   1.0
     */
    
    static function add_tags() {
    	global $sti_global_settings;
    	self::sti_check_ajax_referer( 'sti_add_tags' );
    
    	$post_id = (int) $_REQUEST["post_id"];
    	parse_str( $_REQUEST['form_data'], $tags );
    	
    	if( empty( $tags ) ) exit;
    	
    	wp_set_post_tags( $post_id, $tags, false );
    
    	exit;
    }
    
    /**
     * Handles the AJAX request for rebuilding all posts tags.
     *
     * @access  public
     * @since   1.0
     */
    
    static function rebuild_posts_tags() {
    	global $sti_global_settings;
    	self::sti_check_ajax_referer( 'sti_rebuild_posts_tags' );
    	
    	$posts_per_page = 500;
    	$offset = (int) get_transient( "sti_rebuild_posts_tags_offset" );
    	
    	$wp_query_args = array(
    			'post_type' => $sti_global_settings['plugin_settings']['allowed_post_types'],
    			'orderby' => 'date',
    			'order' => 'DESC',
    			'posts_per_page' => $posts_per_page,
    			'offset' => $offset,
    			'fields' => 'ids',
    			'suppress_filters' => false
    	);
    	
    	$offset += $posts_per_page;
    	$posts = new WP_Query( $wp_query_args );
    	
    	foreach( $posts->posts as $single ) {
    		$post = get_post( $single ); 
    		$tags_relevances = STI_general_functions::get_tags_relevance( $sti_global_settings['plugin_settings']['tags_list'], $post->post_content );
    		$to_add = array_slice( $tags_relevances, 0, $sti_global_settings['plugin_settings']['default_tags_number'] );
    		
    		foreach( $to_add as $tag => $relevance ) 
    			if( $relevance == 0 ) unset( $to_add[$tag] ); //don't add tags with relevance == 0
    		
    		if( $_REQUEST['overwrite'] == "overwrite" )
    			wp_set_post_tags( $single, array_keys( $to_add ), false );
    		else if( $_REQUEST['overwrite'] == "keep" )
    			wp_set_post_tags( $single, array_keys( $to_add ), true );
    		else 
    			die( "Error: invalid overwrite param." );
    	}
    	
    	if( $posts->found_posts > $offset ) {
    		set_transient( "sti_rebuild_posts_tags_offset", $offset );
    		die( sprintf( __( 'Processed %1$s posts of %2$s', 'smart-tag-insert' ), $offset, $posts->found_posts ) );
    	} else { 
    		delete_transient( "sti_rebuild_posts_tags_offset" );
    		die( sprintf( __( 'Completed! Processed %1$s posts of %1$s', 'smart-tag-insert' ), $posts->found_posts ) );
    	}
    }
}
?>