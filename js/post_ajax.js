jQuery(document).ready(function($) {
    
    /* <TAGS SEARCH> */
    $('#sti_load_tags').on("click", function(e) {
        e.preventDefault();
        $('#sti_ajax_loader').css('display', 'inline');
        $('#sti_ajax_error').css('display', 'none');
        
        var data = {
            action: "sti_load_tags",
            _ajax_nonce: sti_post_ajax_vars.nonce_sti_load_tags,
            post_id: $('#sti_load_tags').attr("accesskey")
        };
		
        $.post(ajaxurl, data, function(response) {
            $('#sti_ajax_loader').css('display', 'none');
            
            if(response.indexOf('Error') < 0) {
            	$('#sti_load_tags_response').html(response);
            	$('#sti_add_tags_content').css('display', 'inline');
            } else {
            	$('#sti_ajax_error').html(response);
                $('#sti_ajax_error').css('display', 'inline');
            }
            
        });
    });
    /* </TAGS SEARCH> */
    
    /* <TAGS ADDING> */
    $('#sti_add_tags').on("click", function(e) {
        e.preventDefault();
        $('#sti_ajax_loader').css('display', 'inline');
        $('#sti_ajax_error').css('display', 'none');
        
        var data = {
            action: "sti_add_tags",
            _ajax_nonce: sti_post_ajax_vars.nonce_sti_add_tags,
            post_id: $('#sti_add_tags').attr("accesskey"),
            form_data: $('#sti_load_tags_response :input').serialize()
        };
		
        $.post(ajaxurl, data, function(response) {
            $('#sti_ajax_loader').css('display', 'none');
            
            if(response.indexOf('Error') < 0) {
            	$('#sti_ajax_success').css('display', 'inline');
            } else {
            	$('#sti_ajax_error').html(response);
                $('#sti_ajax_error').css('display', 'inline');
            }
            
        });
    });
    /* </TAGS ADDING> */
    
});