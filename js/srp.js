let srpPostOffset = 0;
jQuery(document).ready(function($){
    $.ajax({
        type: "POST",    
        url:srp_localize_front_data.ajax_url,                    
        dataType: "json",
        data:{
              action:"srp_update_post_views_ajax",
              post_id:srp_localize_front_data.post_id, 
              srp_security_nonce:srp_localize_front_data.srp_security_nonce
            },
        success:function(response){                                                             
        },
        error: function(response){                            
        }
    });

    /* 
    Infinite Scrolling feature js starts here
    @since: 1.5
    */
    $(window).scroll(function () {
        // Check if the user has scrolled to the bottom of the page
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 5) {
            if($('#srp-infinite-scroll').length > 0 && $('#srp-infinite-scroll').val() == 'on'){
                srp_load_related_content(srpPostOffset);
            }
        }
    });

    /*
    Function to check view port
    @since: 1.5
    */
    $.fn.isInViewport = function() {
      var elementTop = $(this).offset().top;
      var elementBottom = elementTop + $(this).outerHeight();

      var viewportTop = $(window).scrollTop();
      var viewportBottom = viewportTop + $(window).height();

      return elementBottom > viewportTop && elementTop < viewportBottom;
    };

    /*
    Function to change the URL on scroll
    @since: 1.5
    */
    $(window).on('resize scroll', function() {
        if($('.srp-post-details').length > 0){
            $('.srp-post-details').each(function() {
                if ($(this).isInViewport()) {
                    let postUrl = $(this).attr("data-url");
                    history.replaceState(null, null, postUrl);
                }
            });
        }
    });



    /*
    Function to load related content when window scroll reached at the bottom
    @since: 1.5
    */
    function srp_load_related_content(offset) {
        let currentPostId = $('#srp-current-post-id').val();
        let currentPostType = $('#srp-current-post-type').val();
        if(currentPostType == 'post'){
            $.ajax({
                type: "POST",
                url:srp_localize_front_data.ajax_url,
                data: {
                    action: 'srp_load_related_content',
                    offset: offset, 
                    post_id: currentPostId, 
                    srp_security_nonce:srp_localize_front_data.srp_security_nonce
                },
                async: false,
                success: function(responseData){
                    let objResp = JSON.parse(responseData);
                    if(objResp.offset && objResp.offset != 'finished'){
                        srpPostOffset = objResp.offset;  
                        if(objResp.post_content.length > 0){
                            let loadedPost = `<div class="srp-post-details" data-url="${objResp.post_parmalink}"><div class="srp-post-title"><h1>${objResp.post_title}</h1></div>${objResp.featured_image}<div class="srp-post-content">${objResp.post_content}</div><div>`;
                            $('#srp-content-wrapper').append(loadedPost); 
                            history.replaceState(null, null, objResp.post_parmalink); 
                            $('html, body').scrollTop($(".srp-post-details").last().offset().top);
                        }  
                    }      
                } 
            }); 
        } 
    }

    /* 
    Function to check if an element is in the viewport
    @since: 1.5
    */
    function isElementInViewport($element) {
        var elementTop = $element.offset().top;
        var elementBottom = elementTop + $element.outerHeight();
        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop + $(window).height();

        return elementBottom > viewportTop && elementTop < viewportBottom;
    }

    // Infinite Scrolling feature js ends here
});