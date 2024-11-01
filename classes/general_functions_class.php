<?php

/**
 * @author Stefano Ottolenghi
 * @copyright 2015
 * @package	STI
 */

class STI_general_functions {
    
	/**
	 * Calculates one tag relevance given the post content.
	 * 
	 * Plugin's engine core.
	 * We consider perfectly finding 10 times the tag in a 360-words text as 100% relevance.
	 * Using this proportion, we find out the weigth each occurrence of the tag in the text should have.
	 * IF the tag is just one word, the relevance is just the number of occurrences times the weigth.
	 * IF the tag is more than one word, the magic begins (are you watching closely?):
	 * 	 The tag is split in words, and a pattern match is run allowing there could be something in-between each word.
	 *   For example, if the tag is "download music", we allow stuff between "download" and "music", so that even "download THE music" is caught.
	 *   The chars found in between the words, however much they may be, are used to calculate the *relevance decrease* caused by not finding the tag perfectly, and then data is put together.   
	 * 
	 * For details on the algorithm, see http://www.thecrowned.org/method-for-pattern-relevance-in-text
	 *
	 * @access  public
	 * @since   1.0
	 * @param   $needle string the tag to be looked for
	 * @param	$haystack string the text to look in
	 * @param	&$overall_relevance int will contain the tag relevance 
	 * @return 	array tag occurrences in text with details
	 */
	
    static function get_tag_relevance( $needle, $haystack, &$overall_relevance ) {
    	if( strlen( $haystack ) == 0 ) { $overall_relevance = 0; return; } //empty text -> 0 relevance for everything
    	
    	//Break the pattern in single words
    	$exploded_needle = explode( " ", $needle );
	    $explode_count = count( $exploded_needle );
	
		//Strip special chars from text and count text words
		$purged_haystack = preg_replace( '/[(),;:!?%#$"_+=\\/-]+/', '', trim( preg_replace( '/\'|&nbsp;|&#160;|\r|\n|\r\n|\s+/', ' ',  strip_tags( $haystack ) ) ) ); //need to trim to remove final new lines
		$haystack_words = count( preg_split( '/\s+/', $purged_haystack, -1, PREG_SPLIT_NO_EMPTY ) );
		
		//Calculate how much a perfect occurrence should weigh
	 	$occurrence_weigth = 10*360/$haystack_words; //we consider perfectly finding 10 times the tag in a 360-words text as 100% relevance
	    
	    //Build the regex pattern
	    $pattern = "^\b";
	    $n = 1;
	    foreach( $exploded_needle as $single ) {
	        $pattern .= $single;
	        
	        if( $n < $explode_count )
	            $pattern .= "(.*?)\b";
		
			++$n;
	    }
	
	    $pattern .= "^i"; //case insensitive
	
	    $output = array();
		$overall_relevance = 0;
	    preg_match_all( $pattern, $haystack, $occurrences, PREG_PATTERN_ORDER );
	    
	    //preg_match_all( $pattern, $haystack, $occurrences_offsets, PREG_OFFSET_CAPTURE ); //this can be useful in computing relevance, maybe early matches should weigh more than later ones
	
		if( count( $occurrences[0] ) == 0 ) return 0;
		
		//If the string to match is just one word, its relevance is its weigth * times it occurres
		if( $explode_count == 1 ) {
			$overall_relevance = count( $occurrences[0] )*$occurrence_weigth;
		
		} else {
			$n = 0;
	    	foreach( $occurrences[1] as $single ) {
				//If pattern words are separated by a full stop, this shouldn't count as a match
				if( strpos( $single, '.' ) !== false ) continue;
				
				//Calculate how much this occurrence contributes to overall relevance
				$single = trim( $single );
				$relevance = $occurrence_weigth - ( pow( strlen( $single ), 4/5 ) );
				$output[] = array(
					"text_in_between" => $single,
					"relevance" => $relevance/*,
					"offset" => $occurrences_offsets[0][$n][1]*/
				);
	
				$overall_relevance += $relevance;
				++$n;
	    	}
		}
	
		if( $overall_relevance > 100 )
			$overall_relevance = 100;
	
	    return $output;
	}
	
	/**
	 * Calculates tags relevances given a list of them and the post content.
	 *
	 * @access  public
	 * @since   1.0
	 * @param   $tags_list array the list of tags
	 * @param	$content string the post content
	 * @return 	array tags relevances
	 */
	
	static function get_tags_relevance( $tags_list, $content ) {
		$relevances = array();
		
		foreach( $tags_list as $single ) {
			$relevance = 0;
			self::get_tag_relevance( $single, $content, $relevance );
			$relevances[$single] = $relevance;
		}
	
		arsort($relevances);
		return $relevances;
	}
}
?>
