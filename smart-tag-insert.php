<?php
/*
Plugin Name: Smart Tag Insert
Plugin URI: http://www.thecrowned.org/wordpress-plugins/smart-tag-insert
Description: Automatically adds most relevant tags to posts selecting them from an admin-defined list. 
Author: Stefano Ottolenghi
Version: 1.0.1
Author URI: http://www.thecrowned.org/
Text Domain: smart-tag-insert
*/

/** Copyright Stefano Ottolenghi 2015
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

//If trying to open this file out of wordpress, warn and exit
if( ! function_exists( 'add_action' ) )
    die( 'This file is not meant to be called directly.' );

require_once( 'classes/general_functions_class.php' );
require_once( 'classes/install_functions_class.php' );
require_once( 'classes/meta_boxes_class.php' );
require_once( 'classes/ajax_functions_class.php' );
require_once( 'classes/options_fields_class.php' );
require_once( 'classes/html_functions_class.php' );

class Smart_Tag_Insert {
    
    function __construct() {
        global $sti_global_settings;
        
        $sti_global_settings['current_version'] = get_option( 'sti_current_version' );
        $sti_global_settings['newest_version'] = '1.0.1';
        $sti_global_settings['option_name'] = 'sti_settings';
        $sti_global_settings['folder_path'] = plugins_url( '/', __FILE__ );
		$sti_global_settings['dir_path'] = plugin_dir_path( __FILE__ );
		$sti_global_settings['options_menu_link'] = admin_url( add_query_arg( array( 'page' => 'sti-options' ), 'options-general.php' ) );
		$sti_global_settings['plugin_settings'] = get_option( $sti_global_settings['option_name'] );
		if( ! is_array( $sti_global_settings['plugin_settings'] ) ) $sti_global_settings['plugin_settings'] = array();

        //Add left menu entries for both stats and options pages
        add_action( 'admin_menu', array( $this, 'admin_menus' ) );
        
        //Hook for the install procedure
        register_activation_hook( __FILE__, array( 'STI_install_functions', 'install' ) );
        
        //Hook on blog adding on multisite wp to install the plugin there either
        add_action( 'wpmu_new_blog', array( 'STI_install_functions', 'new_blog_install' ), 10, 6);
        
		//Plugin update routine
		add_action( 'plugins_loaded', array( $this, 'maybe_update' ) );
		
        //On load plugin pages
        add_action( 'load-settings_page_sti-options', array( $this, 'on_load_options_page' ) );
		
        //Localization
        add_action( 'plugins_loaded', array( $this, 'load_localization' ) );
        
        //Custom links besides the usual "Edit" and "Deactivate"
        add_filter( 'plugin_action_links', array( $this, 'settings_meta_link' ), 10, 2 );
        add_filter( 'plugin_row_meta', array( $this, 'donate_meta_link' ), 10, 2 );
        	
        //Load post.php box stuff
        add_action( 'load-post.php', array( $this, 'on_load_post_page' ) );
        
        //AJAX calls
        add_action( 'wp_ajax_sti_load_tags', array( 'STI_ajax_functions', 'load_tags' ) );
        add_action( 'wp_ajax_sti_add_tags', array( 'STI_ajax_functions', 'add_tags' ) );
        
        //Manage AJAX calls
        add_action( 'wp_ajax_sti_save_settings', array( 'STI_ajax_functions', 'save_settings' ) );
        add_action( 'wp_ajax_sti_rebuild_posts_tags', array( 'STI_ajax_functions', 'rebuild_posts_tags' ) );
	}
    
    /**
     * Adds menu item "Smart Tag Insert"
     *
     * @access  public
     * @since   1.0
     */
    
    function admin_menus() {
        global $sti_global_settings;
        
    	$sti_global_settings['options_menu_slug'] = add_options_page( __( 'Smart Tag Insert', 'smart-tag-insert' ), __( 'Smart Tag Insert', 'smart-tag-insert' ), 'manage_options', 'sti-options', array( $this, 'options_page' ) );
    }
    
    /**
     * If current_version option is DIFFERENT from the latest release number, launch the update procedure.
     *
     * @access  public
     * @since   1.0
     */
    
    function maybe_update() {
        global $sti_global_settings;
        
        if( $sti_global_settings['current_version'] != $sti_global_settings['newest_version'] ) {
            require_once( 'classes/update_class.php' );
            
            STI_update_class::update();
            $sti_global_settings['current_version'] = $sti_global_settings['newest_version'];
            
			/**
			 * Fires after STI has been updated to latest version.
			 * @since 1.0
			 */
			
            do_action( 'sti_updated' );
        }
    }
    
    /**
     * Loads stats metabox in all allowed post-types editing pages.
     *
     * @access  public
     * @since   1.0
     */
    
    function post_page_metabox() {
    	global $sti_global_settings;
    	require_once( 'classes/meta_boxes_class.php' );
    
    	foreach( $sti_global_settings['plugin_settings']['allowed_post_types'] as $post_type )
    		add_meta_box( 'autotagger', __( 'Smart Tag Insert', 'smart-tag-insert' ), array( 'STI_meta_boxes', 'post_page_metabox' ), $post_type, 'side', 'default' );
    }
    
    /**
     * Loads plugin's css and js in post editing page.
     *
     * @access  public
     * @since   1.0
     */
    
    function on_load_post_page() {
    	global $sti_global_settings, $current_user;
    
    	//Load Metaboxes in post.php
    	add_action( 'add_meta_boxes', array( $this, 'post_page_metabox' ) );
    
    	wp_enqueue_style( 'sti_style', $sti_global_settings['folder_path'].'style/style.css', array( 'wp-admin' ) );
    	wp_enqueue_script( 'sti_post_ajax', $sti_global_settings['folder_path'].'js/post_ajax.js', array( 'jquery' ) );
    	wp_localize_script( 'sti_post_ajax', 'sti_post_ajax_vars', array(
    		'nonce_sti_load_tags' => wp_create_nonce( 'sti_load_tags' ),
    		'nonce_sti_add_tags' => wp_create_nonce( 'sti_add_tags' )
    	) );
    	
    	//Hide custom tagging capabilities if user shouldn't see them
    	if( array_intersect( $current_user->roles, $sti_global_settings['plugin_settings']['user_roles_allowed_to_add_tags'] ) == false )
    		wp_enqueue_style( 'sti_hide_tagging_cap', $sti_global_settings['folder_path'].'style/hide_tags.css', array( 'wp-admin' ) );
    }
    
    /**
     * Loads metaboxes and in the plugin options page and all the js and css needed, plus the strings js needs (nonces and localized text).
     *
     * @access  public
     * @since   1.0
     */
    
    function on_load_options_page() {
        global $sti_global_settings;
        wp_enqueue_script( 'post' );
        
        add_meta_box( 'sti_settings', __( 'Settings', 'smart-tag-insert' ), array( 'STI_meta_boxes', 'settings' ), $sti_global_settings['options_menu_slug'], 'normal', 'default', $sti_global_settings['plugin_settings'] );
        add_meta_box( 'sti_posts_tags_rebuild', __( 'Posts tags rebuilder', 'smart-tag-insert' ), array( 'STI_meta_boxes', 'posts_tags_rebuilder' ), $sti_global_settings['options_menu_slug'], 'side', 'default' );
        add_meta_box( 'sti_support_the_fucking_author', __( 'Support the author', 'smart-tag-insert' ), array( 'STI_meta_boxes', 'support_the_fucking_author' ), $sti_global_settings['options_menu_slug'], 'side', 'default' );
        
        wp_enqueue_style( 'sti_header_style', $sti_global_settings['folder_path'].'style/header_style.css', array( 'wp-admin' ) );
		wp_enqueue_style( 'sti_style', $sti_global_settings['folder_path'].'style/style.css', array( 'wp-admin' ) );
        wp_enqueue_script( 'sti_options_ajax_stuff', $sti_global_settings['folder_path'].'js/options_ajax_stuff.js', array( 'jquery' ) );
        wp_localize_script( 'sti_options_ajax_stuff', 'sti_options_ajax_stuff_vars', array(
            'nonce_sti_save_settings' => wp_create_nonce( 'sti_save_settings' ),
            'nonce_sti_rebuild_posts_tags' => wp_create_nonce( 'sti_rebuild_posts_tags' ),
            'sti_options_url' => $sti_global_settings['options_menu_link']
        ) );
        
		/**
		 * Fires on STI options page load.
		 * 
		 * Equivalent to load-options_page-sti-options but recommended, as fires after all STI matters have been dealt with. 
		 * 
		 * @since 	1.0
		 */
		
        do_action( 'sti_on_load_options_page' );
    }
    
	
    /**
     * Loads localization files
     *
     * @access  public
     * @since   1.0
     */
    
    function load_localization() {
        load_plugin_textdomain( 'smart-tag-insert', false, dirname( plugin_basename( __FILE__ ) ).'/lang/' );
    }
    
    /**
     * Shows the "Settings" link in the plugins list (under the title)
     *
     * @access  public
     * @since   1.0
     * @param   $links array links already in place
     * @param   $file string current plugin-file
     */
    
    function settings_meta_link( $links, $file ) {
        global $sti_global_settings;
       
       if( $file == plugin_basename( __FILE__ ) )
            $links[] = '<a href="'.admin_url( $sti_global_settings['options_menu_link'] ).'" title="'.__( 'Settings', 'smart-tag-insert' ).'">'.__( 'Settings', 'smart-tag-insert' ).'</a>';
     
        return $links;
    }
    
    /**
     * Shows the "Donate" and "Go PRO" links in the plugins list (under the description)
     *
     * @access  public
     * @since   1.0
     * @param   $links array links already in place
     * @param   $file string current plugin-file
     */
    
    function donate_meta_link( $links, $file ) {
       if( $file == plugin_basename( __FILE__ ) ) {
            $links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SM5Q9BVU4RT22" title="'.__( 'Donate', 'smart-tag-insert' ).'">'.__( 'Donate', 'smart-tag-insert' ).'</a>';
			$links[] = '<a href="http://www.thecrowned.org/post-pay-counter-pro?utm_source=users_site&utm_medium=plugins_list&utm_campaign=ppcp" title="'.__( 'Go PRO', 'smart-tag-insert' ).'">'.__( 'Go PRO', 'smart-tag-insert' ).'</a>';
			$links[] = '<a href="http://www.thecrowned.org/post-pay-counter-extensions?utm_source=users_site&utm_medium=plugins_list&utm_campaign=ppc_addons" title="'.__( 'Addons', 'smart-tag-insert' ).'">'.__( 'Addons', 'smart-tag-insert' ).'</a>';
       }
     
        return $links;
    }
    
    /**
     * Shows the Options page
     *
     * @access  public
     * @since   1.0
     */
    
    function options_page() {
        global $sti_global_settings;
        ?>
		
<div class="wrap">
	
	<?php STI_HTML_functions::display_header_logo(); ?>
	
	<div id="sti_header">
		<div id="sti_header_text">
			<div id="sti_header_links">
			<?php 
			
			/**
			 * Filters installed version text displayed in upper-right section of the options page. 
			 * 
			 * @since	2.0
			 * @param	string installed version text (whole).
			 */
			 
			echo apply_filters( 'sti_options_installed_version', __( 'Installed version' , 'smart-tag-insert' ).': '.$sti_global_settings['current_version'] ); 
			?>
			</div>
			<h2>Smart Tag Insert - <?php _e( 'Options', 'smart-tag-insert' ); ?></h2>
			<p><?php _e( 'Define a list of tags through the textarea below, and the plugin will add a box in the post editing page through which look for relevant tags basing on the post content. The most relevant will automatically be selected (although the selection can be changed), and selected tags can be added to the post with a click. You can even rebuild all posts\' tags through the Posts Tags Rebuilder.', 'smart-tag-insert' ); ?></p>
		</div>
	</div>
	
			<?php
        
		/**
		 * Fires before any metabox has been displayed in options page.
		 *
		 * @since	1.0
		 */
		
        do_action( 'sti_html_options_before_boxes' );
        
        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
        wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
        ?>
		
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="post-body" class="has-sidebar">
			<div id="post-body-content" class="has-sidebar-content">
        
		<?php
        do_meta_boxes( $sti_global_settings['options_menu_slug'], 'normal', null );
        ?>
		
			</div>
		</div>
		<div id="side-info-column" class="inner-sidebar">
        
		<?php
        do_meta_boxes( $sti_global_settings['options_menu_slug'], 'side', null );
        ?>
		
		</div>
	</div>
</div>
		
		<?php
    }
}

global $sti_global_settings;
$sti_global_settings = array();
new Smart_Tag_Insert();
?>
