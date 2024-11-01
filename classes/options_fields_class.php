<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2015
 * @package	STI
 */

class STI_options_fields {
    
    /**
     * Generates a maybe checked radio field
     *
     * @access  public
     * @since   1.0
     * @param   $setting int current setting value (either 0 or 1)
     * @param   $name string field name
     * @param   $value string field value
     * @param   $id string field id
	 * @param 	$disabled bool whether field should be disabled
     * @return  string the html of the radio field
    */
    
    static function generate_radio_field( $setting, $name, $value, $id, $disabled ) {
        $disabled_html = '';
		if( $disabled )
			$disabled_html = ' disabled="disabled"';
		
		$checked_html = '';
		if( $setting )
            $checked_html = ' checked="checked"';
		
		return '<input type="radio" name="'.$name.'" value="'.$value.'" id="'.$id.'" '.$checked_html.$disabled_html.'/>';
    }
    
    /**
     * Generates a maybe checked checkbox field
     *
     * @access  public
     * @since   1.0
     * @param   $setting int current setting value (either 0 or 1)
     * @param   $name string field name
     * @param   $id string field id
	 * @param 	$disabled bool whether field should be disabled
     * @return  string the html of the checkbox field
    */
            
    static function generate_checkbox_field( $setting, $name, $value, $id, $disabled ) {
        $disabled_html = '';
		if( $disabled )
			$disabled_html = ' disabled="disabled"';
		
		$checked_html = '';
		if( $setting )
            $checked_html = ' checked="checked"';
		
		return '<input type="checkbox" name="'.$name.'" value="'.$value.'" id="'.$id.'" '.$checked_html.$disabled_html.'/>';
    }
        
    /**
     * Checks whether the given value is set or not. Sets $checkbox to NULL so we'll later know what vars are still to be dealt with
     *
     * @access  public
     * @since   1.0
     * @param   $checkbox int checkbox value
     * @return  bool checkbox status
    */
    
    static function get_checkbox_value( &$checkbox ) {
        if( ! isset( $checkbox ) )
            return 0;
        else
            return 1;
		
		$checkbox = NULL;
    }
    
    /**
     * Gets a radio-set value. All three possibilities are set to zero in an array. Switch through the $radio and check which one was selected. The selected option has its value turned to 1 in the return array, while others are still 0. Set $radio to NULL so we'll later know what vars are still to be dealt with.
     *
     * @access  public
     * @since   1.0
     * @param   $radio string the value of the checked radio
     * @param   $opt_1 string the value of the first option
     * @param   $opt_2 string the value of the second option
     * @param   $opt_3 string optional the value of the third option
     * @return  array the 2/3 possibilities along with their set values
    */
    
    static function get_radio_value( &$radio, $opt_1, $opt_2, $opt_3 = FALSE ) {
        $return = array(
            $opt_1 => 0,
            $opt_2 => 0,
        );
        
        if( $opt_3 )
            $return[$opt_3] = 0;
        
        switch( $radio ) {
            case $opt_1:
                $return[$opt_1] = 1;
                break;
                
            case $opt_2:
                $return[$opt_2] = 1;
                break;
                
            case $opt_3:
                $return[$opt_3] = 1;
                break;
        }
        
        $radio = null;
        return $return;
    }
}
?>