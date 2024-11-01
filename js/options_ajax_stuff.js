jQuery(document).ready(function($) {
    
    /* <SETTINGS BOX CALLS> */
    $('#sti_save_settings').on("click", function(e) {
        e.preventDefault();
        $('#sti_settings_ajax_loader').css('display', 'inline');
        $('#sti_settings_error').css('display', 'none');
		$('#sti_settings_success').css('display', 'none');
        
        var data = {
            action: "sti_save_settings",
            _ajax_nonce: sti_options_ajax_stuff_vars.nonce_sti_save_settings,
            form_data: $('#sti_settings_form').serialize()
        };
        
        $.post(ajaxurl, data, function(response) {
            $('#sti_settings_ajax_loader').css('display', 'none');

            if(response.indexOf('Error') >= 0) {
            	$('#sti_settings_error').html(response);
                $('#sti_settings_error').css('display', 'inline');
            } else if(response.indexOf('Warning') >= 0) {
        		$('#sti_settings_error').html(response);
                $('#sti_settings_error').css('display', 'inline');
                $('#sti_settings_error').css('background-color', 'orange');
            } else {
            	$('#sti_settings_success').css('display', 'inline');
            }
        });
    });
    /* </SETTINGS BOX CALLS> */
    
    /* <REBUILD BOX CALLS> */
    $('.sti_rebuild_posts_tags_button').on("click", function(e) {
        e.preventDefault();
        $('#sti_rebuild_ajax_loader').css('display', 'inline');
        $('#sti_rebuild_error').css('display', 'none');
		$('#sti_rebuild_success').css('display', 'none');
        
		if($(this).attr("id") == 'sti_rebuild_posts_tags_overwrite')
	        ajaxCallRebuild("overwrite");
    	else
    		ajaxCallRebuild("keep");
    });
    
    function ajaxCallRebuild(overwrite) {
    	
    	var data = {
            action: "sti_rebuild_posts_tags",
            overwrite: overwrite,
            _ajax_nonce: sti_options_ajax_stuff_vars.nonce_sti_rebuild_posts_tags
        };
    	
    	$.post(ajaxurl, data, function(response) {

            if(response.indexOf('Error') < 0) {
            	$('#sti_rebuild_info').css('display', 'inline');
            	$('#sti_rebuild_info').css('background-color', '#FFCC00');
            	$('#sti_rebuild_info').html(response);
            	
            	if(response.indexOf('Completed') < 0) { 
            		ajaxCallRebuild();
            	} else {
            		$('#sti_rebuild_info').css('background-color', '#BCED91');
            		$('#sti_rebuild_ajax_loader').css('display', 'none');
            	}
            	
            } else {
            	$('#sti_rebuild_error').html(response);
                $('#sti_rebuild_error').css('display', 'inline');
                $('#sti_rebuild_ajax_loader').css('display', 'none');
            }
        });
    }
    /* </REBUILD BOX CALLS> */
    
});