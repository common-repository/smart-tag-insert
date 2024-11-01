<?php

/**
 * @package STI
 * @author Stefano Ottolenghi
 * @copyright 2015
 */

class STI_HTML_functions {
    
	/**
	 * Displays header logo and caption
	 *
	 * @access	public
	 * @since	1.0
	 */
	
	static function display_header_logo() {
		global $sti_global_settings;
		?>
		
		<div id="sti_logo">
			<img src="<?php echo $sti_global_settings['folder_path'].'style/images/pengu-ins.png'; ?>" />
			<div id="sti_logo_caption"><?php printf( __( 'A %1$spengu-ins%2$s production', 'smart-tag-insert' ), '<a href="http://www.thecrowned.org/pengu-ins?utm_source=users_site&utm_medium=header_logo&utm_campaign=pengu-ins" title="Pengu-ins" target="_blank">', '</a>' ); ?></div>
		</div>
		
		<?php
	}
    
    /**
     * Prints settings fields enclosing them in a <p>: a checkbox/radio in a floated-left span, the tooltip info on the right and the description in the middle.
     *
     * @access  public
     * @since   1.0
     * @param   $text string the field description
     * @param   $setting string the current setting value
     * @param   $field string the input type (checkbox or radio)
     * @param   $name string the field name
     * @param   $tooltip_description string optional the tooltip description
     * @param   $value string optional the field value (for radio)
     * @param   $id string optional the field id
     * @return  string the html 
    */
    
    static function echo_p_field( $text, $setting, $field, $name, $tooltip_description = NULL, $value = NULL, $id = NULL, $disabled = false ) {
	   global $sti_global_settings;
		
        $html = '<p style="height: 11px;">';
        
		if( is_string( $tooltip_description ) ) {
			$html .= '<span class="sti_tooltip">';
			$html .= '<img src="'.$sti_global_settings['folder_path'].'style/images/info.png'.'" title="'.$tooltip_description.'" class="sti_tooltip_container" />';
			$html .= '</span>';
		}
		
        $html .= '<label>';
        $html .= '<span class="checkable_input">';
         
        if( $field == 'radio' )
            $html .= STI_options_fields::generate_radio_field( $setting, $name, $value, $id, $disabled ); 
        else if( $field == 'checkbox' )
            $html .= STI_options_fields::generate_checkbox_field( $setting, $name, $value, $id, $disabled ); 
                
        $html .= '</span>';
        $html .= $text;
        $html .= '</label>';
        $html .= '</p>';
        
        return apply_filters( 'sti_settings_field_generation', $html );
    }
    
    /**
     * Prints settings fields enclosing them in a <p>: a checkbox/radio in a floated-left span, the tooltip info on the right and the description in the middle.
     *
     * @access  public
     * @since   1.0
     * @param   $field_name string the field name
     * @param   $field_value string the field value
     * @param   $label_text string the label text
     * @param   $size int optional the text field size
     * @return  string the html
    */
    
    static function echo_text_field( $field_name, $field_value, $label_text, $size = 15, $placeholder = '' ) {
        if( ! empty( $placeholder ) )
			$placeholder = ' placeholder="'.$placeholder.'"';
		
		$html = '<p>';
        $html .= '<label for="'.$field_name.'">'.$label_text.'</label>';
        $html .= '<input type="text" name="'.$field_name.'" id="'.$field_name.'" size="'.$size.'" value="'.$field_value.'" class="sti_align_right"'.$placeholder.' />';
        $html .= '</p>';
        
        return apply_filters( 'sti_text_field_generation', $html );
    }
}
?>