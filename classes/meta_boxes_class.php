<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2015
 * @package	STI
 */

class STI_meta_boxes {
    
    /**
     * Displays the Settings metabox in the Options page  
     *
     * @access  public
     * @since   1.0
     * @param   object WP post object
     * @param   array plugin settings
     */
    
    static function settings( $post, $plugin_settings ) {
        global $sti_global_settings, $wp_roles;
        $plugin_settings = $plugin_settings['args'];
        
        echo '<form action="" id="sti_settings_form" method="post">';
        echo '<div class="sti_section">';
        echo '<div class="sti_title">'.__( 'Tags list' , 'smart-tag-insert').'</div>';
        echo '<div class="main">';
        echo '<p>'.sprintf( __( 'Insert tags separated by newline. This is your %1$stag database%2$s: for each post, the plugin will see which ones fit best for the post content.', 'smart-tag-insert' ), '<em>', '</em>' ).'</p>';
        
        echo '<textarea style="width: 100%; height: 100px;" name="tags_list" id="tags_list">'.implode( "\r\n", $plugin_settings['tags_list'] ).'</textarea>';
        echo '<div class="clear"></div>';
        echo '</div>';
        echo '</div>';
        do_action( 'sti_settings_after_tags_list', $plugin_settings );
        
        //Post types to be included in countings
        echo '<div class="sti_section">';
        echo '<div class="sti_title">'.__( 'Allowed post types' , 'smart-tag-insert').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Choose the post types you would like the plugin to work with.', 'smart-tag-insert').'</p>';
        
        $all_post_types = get_post_types();
        $allowed_post_types = $plugin_settings['allowed_post_types'];
        
        foreach ( $all_post_types as $single ) {
            $checked = '';
            
            if( in_array( $single, $allowed_post_types ) )
                $checked = 'checked="checked"';
                
            echo '<input type="checkbox" name="post_type_'.$single.'" id="post_type_'.$single.'" value="'.$single.'" '.$checked.' />';
            echo '<label for="post_type_'.$single.'">'.ucfirst( $single ).'</label>';
            echo '<br />';
        }
        
        echo '</div>';
        echo '</div>';
        do_action( 'sti_settings_after_included_post_types', $plugin_settings );
        
        //User roles allowed to add tags through the WP box
        echo '<div class="sti_section">';
        echo '<div class="sti_title">'.__( 'User roles allowed to add new tags' , 'smart-tag-insert').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Choose the user roles that should still be allowed to add new custom tags through the WordPress Tags box in a post page. Other user roles will have the feature hidden.', 'smart-tag-insert').'</p>';
        
        foreach( $wp_roles->role_names as $key => $value ) {
        	$checked = '';
        
        	if( in_array( $key, $plugin_settings['user_roles_allowed_to_add_tags'] ) )
        		$checked = 'checked="checked"';
        
        	echo '<input type="checkbox" name="user_role_tag_'.$key.'" id="user_role_tag_'.$key.'" value="'.$key.'" '.$checked.' />';
        	echo '<label for="user_role_tag_'.$key.'">'.$value.'</label>';
        	echo '<br />';
        }
        
        echo '</div>';
        echo '</div>';
        do_action( 'sti_settings_after_user_roles_allowed_to_add_tags', $plugin_settings );
        
        //Miscellanea
        echo '<div class="sti_section">';
        echo '<div class="sti_title">'.__( 'Number of tags per post' , 'smart-tag-insert').'</div>';
        echo '<div class="main">';
        echo '<p>'.__( 'Input the number of tags you would like to be automatically selected when assigning tags to a post. This will be the default number in all automatic processes (posts tags rebuilder).', 'smart-tag-insert').'</p>';
        echo STI_HTML_functions::echo_text_field( 'default_tags_number', $plugin_settings['default_tags_number'], __( 'Number of tags' , 'smart-tag-insert') );
        echo '</div>';
        echo '</div>';
        do_action( 'sti_settings_after_default_tags_number', $plugin_settings );
        ?>
        
        <div class="sti_save_success" id="sti_settings_success"><?php _e( 'Settings were successfully updated.' , 'at' ); ?></div>
        <div class="sti_save_error" id="sti_settings_error"></div>
        <div class="sti_save_settings">
            <img src="<?php echo $sti_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading' , 'smart-tag-insert'); ?>" alt="<?php _e( 'Loading' , 'smart-tag-insert'); ?>" class="sti_ajax_loader" id="sti_settings_ajax_loader" />
            <input type="submit" class="button-primary" name="sti_save_settings" id="sti_save_settings" value="<?php _e( 'Save' , 'smart-tag-insert') ?>" />
        </div>
        <div class="clear"></div>
        </form>
    <?php }
    
    /**
     * Displays the metabox "Support the author" in the Options page
     *
     * @access  public
     * @since   1.0
     */
    
    static function support_the_fucking_author() {
    	global $sti_global_settings;
    
    	echo '<p>'.__( 'If you like the Post Pay Counter, there are a couple of crucial things you can do to support its development' , 'smart-tag-insert').':</p>';
    	echo '<ul style="margin: 0 0 15px 2em; padding: 0">';
    	echo '<li style="list-style-image: url(\''.$sti_global_settings['folder_path'].'style/images/paypal.png\');"><a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=M5MQUUJ2N8K66" title="'.__( 'Donate money' , 'smart-tag-insert').'"><strong>'.__( 'Donate money' , 'smart-tag-insert').'</strong></a>. '.__( 'Plugins do not write themselves: they need time and effort, and I give all of that free of charge. Donations of every amount are absolutely welcome.' , 'smart-tag-insert').'</li>';
    	echo '<li style="list-style-image: url(\''.$sti_global_settings['folder_path'].'style/images/amazon.png\');">'.sprintf( __( 'Give me something from my %1$sAmazon Wishlist%2$s.' , 'smart-tag-insert'), '<a target="_blank" href="http://www.amazon.it/registry/wishlist/1JWAS1MWTLROQ" title="Amazon Wishlist">', '</a>' ).'</li>';
    	//echo '<li style="list-style-image: url(\''.$sti_global_settings['folder_path'].'style/images/star.png\');">'.sprintf( __( 'Rate it in the %1$sWordpress Directory%3$s and share the %2$sofficial page%3$s.' , 'smart-tag-insert'), '<a target="_blank" href="http://wordpress.org/extend/plugins/post-pay-counter/" title="Wordpress directory">', '<a target="_blank" href="http://www.thecrowned.org/wordpress-plugins/post-pay-counter" title="Official plugin page">', '</a>' ).'</li>';
    	echo '<li style="list-style-image: url(\''.$sti_global_settings['folder_path'].'style/images/write.png\');">'.__( 'Have a blog or write on some website? Write about the plugin and email me the review!' , 'smart-tag-insert').'</li>';
    	echo '</ul>';
    }
    
    /**
     * Displays the metabox Tags Rebuilder in the options page.
     *
     * @access  public
     * @since   1.0
     */
    
    static function posts_tags_rebuilder() {
    	global $sti_global_settings;
    	?>
    <p>
    	<?php printf( __( 'The buttons below automatically assign the most relevant tags to each post belonging to the chosen post types. The number of tags to be assigned can be set in the %1$s box.', 'at' ), "<em>".__( "Settings", "at" )."</em>" ); ?>
    </p>
    <p>
    	<?php printf( __( 'The first button will assign the new tags keeping old ones as well, whereas the second button will %1$s remove all already assigned tags to posts and add the new ones, and cannot be undone%2$s.', 'at' ), "<strong>", "</strong>", "<em>".__( "Settings", "at" )."</em>" ); ?>
    </p>
    <p>
        <input type="button" name="sti_rebuild_posts_tags_keep" id="sti_rebuild_posts_tags_keep" value="<?php _e( 'Rebuild posts tags keeping current tags', 'smart-tag-insert'); ?>" class="button-secondary sti_rebuild_posts_tags_button" />
        <div class="clear"></div>
        <input type="button" name="sti_rebuild_posts_tags_overwrite" id="sti_rebuild_posts_tags_overwrite" value="<?php _e( 'Rebuild posts tags replacing current tags', 'smart-tag-insert'); ?>" class="button-secondary sti_rebuild_posts_tags_button" />
        <img src="<?php echo $sti_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading', 'smart-tag-insert'); ?>" alt="<?php _e( 'Loading', 'smart-tag-insert'); ?>" class="sti_ajax_loader" id="sti_rebuild_ajax_loader" />
    </p>

    <div class="sti_save_error" id="sti_rebuild_ajax_error"></div>
    <div class="sti_save_success" id="sti_rebuild_info"></div>
    <div class="clear"></div>
        
    	<?php
    }
    
    /**
     * Displays plugin metabox in the post editing page.
     *
     * @access  public
     * @since   1.0
     * @param   $post object WP post object
     */
    
    static function post_page_metabox( $post ) {
    	global $sti_global_settings;
    	?>
        <p>
        	<?php _e( "Click the button below to find tags that relate with this post (make sure you have saved the post before). Found tags will be displayed along with their relevance. The first 3 will automatically be ticked, but you can choose which ones should be added.", "at" ); ?>
        </p>
        <p>
            <input type="button" name="sti_load_tags" accesskey="<?php echo $post->ID; ?>" id="sti_load_tags" value="<?php _e( 'Search tags', 'smart-tag-insert'); ?>" class="button-secondary" />
        </p>
            
        <div id="sti_load_tags_response"></div>
        
        <div id="sti_add_tags_content" style="display: none;">
    	    <p>
    	    	<?php _e( "Click the button below to add the selected tags to this post. Selected tags will replace all already assigned tags.", "at" ); ?>
    	    </p>
    	    <p>
    	        <input type="button" name="sti_add_tags" accesskey="<?php echo $post->ID; ?>" id="sti_add_tags" value="<?php _e( 'Add selected tags', 'smart-tag-insert'); ?>" class="button-secondary" />
    	        
    	    </p>
        </div>
        
        <img src="<?php echo $sti_global_settings['folder_path'].'style/images/ajax-loader.gif'; ?>" title="<?php _e( 'Loading', 'smart-tag-insert'); ?>" alt="<?php _e( 'Loading', 'smart-tag-insert'); ?>" class="sti_ajax_loader" id="sti_ajax_loader" />
        <div class="sti_save_error" id="sti_ajax_error"></div>
        <div class="sti_save_success" id="sti_ajax_success"><?php _e( "Selected tags have been added successfully.", 'smart-tag-insert' ); ?></div>
        <div class="clear"></div>
            
        	<?php
        }
}
?>