jQuery(document).ready(function($){

    function srpp_start_caching_ajax(current){
        current.addClass('updating-message');
        $.get( ajaxurl,{                    
            action:"srpp_start_posts_caching", 
            srp_security_nonce:srp_localize_data.srp_security_nonce
            }, function(response) {
                $("#srp-percentage-div").addClass('srpp_dnone');
                current.removeClass('updating-message');                 
                if(response.status === 'continue'){
                    $(".srpp_progress_bar").removeClass('srpp_dnone');
                    $(".srpp_progress_bar_body").css("width", response.percentage);
                    $(".srpp_progress_bar_body").text(response.percentage);
                    srpp_start_caching_ajax(current);
                }
                if(response.status === 'finished'){  
                    $(".srpp_progress_bar_body").css("width", response.percentage);
                    $(".srpp_progress_bar_body").text(response.percentage);
                    $(".srpp_progress_bar").addClass('srpp_dnone');         
                    alert('Cached Successfully'); 
                    window.location.reload();  
                }                
        },'json')
        .done(function() {                    
            console.log( "second success" );
        })
        .fail(function() {
            current.removeClass('updating-message');             
            alert('Process broke. Click on Start again');        
        })
        .always(function() {
            //current.removeClass('updating-message'); 
            console.log( "finished" );
        });

    }

    function srpp_start_reset_posts_ajax(current){
        current.addClass('updating-message');
        $.get( ajaxurl,{                    
            action:"srpp_start_posts_reset", 
            srp_security_nonce:srp_localize_data.srp_security_nonce
            }, function(response) {                
                
                current.removeClass('updating-message');                 
                if(response.status === 'cleared'){
                    alert('Cleared Successfully');  
                    location.reload();      
                }
                if(response.status === 'failed'){                       
                    alert('Cache is already cleared or something went wrong');  
                    location.reload();      
                }                
        },'json')
        .done(function() {        
            console.log( "second success" );
        })
        .fail(function() {
            current.removeClass('updating-message');             
            alert('Process broke. Click on Start again');        
        })
        .always(function() {
            //current.removeClass('updating-message'); 
            console.log( "finished" );
        });

    }

    $("#start-caching-btn").click(function(){
            var current = $(this);
            srpp_start_caching_ajax(current);
    });

    $("#start-reseting-post-btn").click(function(){
        var result = confirm("Are you sure you want to Clear Cache?");
        var current = $(this);
        if (result) {
            srpp_start_reset_posts_ajax(current);
        }
    });

    $("#adv_filter_check_1").click(function(){
        if($(this).is(':checked')){
            $("#filter_options").show();
            $("#filter_options tr").show();
        }else{
            $("#filter_options").hide();
        }
        });
        $("#adv_filter_check_2").click(function(){
        if($(this).is(':checked')){
            $("#filter_options").show();
            $("#filter_options tr").show();
        }else{
            $("#filter_options").hide();
        }
        });
        $("#adv_filter_check_3").click(function(){
        if($(this).is(':checked')){
            $("#filter_options").show();
            $("#filter_options tr").show();
        }else{
            $("#filter_options").hide();
        }
        });

        $("#post_excerpt").change(function(){
            if($('#post_excerpt').val() == 'true'){
                $("#excerpt_length_1").parents('tr').show();
            }else{
                $("#excerpt_length_1").parents('tr').hide();
            }
        });

        $("#post_excerpt_2").change(function(){
            if($('#post_excerpt_2').val() == 'true'){
                $("#excerpt_length_2").parents('tr').show();
            }else{
                $("#excerpt_length_2").parents('tr').hide();
            }
        });

        $("#post_excerpt_3").change(function(){
            if($('#post_excerpt_3').val() == 'true'){
                $("#excerpt_length_3").parents('tr').show();
            }else{
                $("#excerpt_length_3").parents('tr').hide();
            }
        });


        $("#pstn_rel_1").change(function(){
            $('#re_position_type_1 option').removeAttr("selected");
            if($('#pstn_rel_1').val() == 'ibc'){
                $("#para_rel_1").parents('tr').show();
                $("#re_position_type_1").parents('tr').show();
                $("#para_percent_1").parents('tr').hide();
            }else{
                $("#para_rel_1").parents('tr').hide();
                $("#re_position_type_1").parents('tr').hide();
                $("#para_percent_1").parents('tr').hide();
            }

            if($('#pstn_rel_1').val() == 'sc'){
                $("#shortcode_1").parents('tr').show();
                $("#para_percent_1").parents('tr').hide();
            }else{
                $("#shortcode_1").parents('tr').hide();
                $("#para_percent_1").parents('tr').hide();
            }
        });
        $("#pstn_rel_2").change(function(){
            $('#re_position_type_2 option').removeAttr("selected");
            if($('#pstn_rel_2').val() == 'ibc'){
                $("#para_rel_2").parents('tr').show();
                $("#re_position_type_2").parents('tr').show();
                $("#para_percent_2").parents('tr').hide();
            }else{
                $("#para_rel_2").parents('tr').hide();
                $("#re_position_type_2").parents('tr').hide();
                $("#para_percent_2").parents('tr').hide();
            }

            if($('#pstn_rel_2').val() == 'sc'){
              $("#shortcode_2").parents('tr').show();
              $("#para_percent_2").parents('tr').hide();
            }else{
             $("#shortcode_2").parents('tr').hide();
             $("#para_percent_2").parents('tr').hide();
            }
        });
        $("#pstn_rel_3").change(function(){
            $('#re_position_type_3 option').removeAttr("selected");
            if($('#pstn_rel_3').val() == 'ibc'){
                $("#para_rel_3").parents('tr').show();
                $("#re_position_type_3").parents('tr').show();
                $("#para_percent_3").parents('tr').hide();
            }else{
                $("#para_rel_3").parents('tr').hide();
                $("#re_position_type_3").parents('tr').hide();
                $("#para_percent_3").parents('tr').hide();
            }

            if($('#pstn_rel_3').val() == 'sc'){
               $("#shortcode_3").parents('tr').show();
               $("#para_percent_3").parents('tr').hide();
            }else{
              $("#shortcode_3").parents('tr').hide();
              $("#para_percent_3").parents('tr').hide();
            }
        });

        $("#re_position_type_1").change(function(){
            if($('#re_position_type_1').val() == 'number_of_paragraph'){
                $("#para_rel_1").parents('tr').show();
            }else{
                $("#para_rel_1").parents('tr').hide();
            }

            if($('#re_position_type_1').val() == '50_of_the_content'){
                $("#para_percent_1").parents('tr').show();
            }else{
                $("#para_percent_1").parents('tr').hide();
            }
        });

        $("#re_position_type_2").change(function(){
            if($('#re_position_type_2').val() == 'number_of_paragraph'){
                $("#para_rel_2").parents('tr').show();
            }else{
                $("#para_rel_2").parents('tr').hide();
            }

            if($('#re_position_type_2').val() == '50_of_the_content'){
                $("#para_percent_2").parents('tr').show();
            }else{
                $("#para_percent_2").parents('tr').hide();
            }
        });

        $("#re_position_type_3").change(function(){
            if($('#re_position_type_3').val() == 'number_of_paragraph'){
                $("#para_rel_3").parents('tr').show();
            }else{
                $("#para_rel_3").parents('tr').hide();
            }

            if($('#re_position_type_3').val() == '50_of_the_content'){
                $("#para_percent_3").parents('tr').show();
            }else{
                $("#para_percent_3").parents('tr').hide();
            }
        });

        $("#re_design_1").change(function(){
            var img_src = $('.suprp_image_path').val();
            $('.suprp-design-related-img').attr('src', '');

            if($('#re_design_1').val() == 'd1'){
             $('.suprp-design-related-img').attr('src', img_src+'design1.jpg');
            }
            if($('#re_design_1').val() == 'd2'){
                $('.suprp-design-related-img').attr('src', img_src+'design2.jpg');
            }
            if($('#re_design_1').val() == 'd3'){
                $('.suprp-design-related-img').attr('src', img_src+'design3.jpg');
            }
        });

        $("#re_design_2").change(function(){
            var img_src = $('.suprp_image_path').val();
            $('.suprp-design-related-img').attr('src', '');
            if($('#re_design_2').val() == 'd1'){
             $('.suprp-design-related-img').attr('src', img_src+'design1.jpg');
            }
            if($('#re_design_2').val() == 'd2'){
                $('.suprp-design-related-img').attr('src', img_src+'design2.jpg');
            }
            if($('#re_design_2').val() == 'd3'){
                $('.suprp-design-related-img').attr('src', img_src+'design3.jpg');
            }
        });

        $("#re_design_3").change(function(){
            var img_src = $('.suprp_image_path').val();
            $('.suprp-design-related-img').attr('src', '');

            if($('#re_design_3').val() == 'd1'){
             $('.suprp-design-related-img').attr('src', img_src+'design1.jpg');
            }
            if($('#re_design_3').val() == 'd2'){
                $('.suprp-design-related-img').attr('src', img_src+'design2.jpg');
            }
            if($('#re_design_3').val() == 'd3'){
                $('.suprp-design-related-img').attr('src', img_src+'design3.jpg');
            }
        });

        $("#sort_by_1").change(function(){
        if($('#sort_by_1').val() == 'popular'){
           $("#age1-direction").parents('tr').show();
        }else{
           $("#age1-direction").parents('tr').hide();
        }
        });

        $("#sort_by_2").change(function(){
        if($('#sort_by_2').val() == 'popular'){
           $("#age2-direction").parents('tr').show();
        }else{
           $("#age2-direction").parents('tr').hide();
        }
        });

        $("#sort_by_3").change(function(){
        if($('#sort_by_3').val() == 'popular'){
           $("#age3-direction").parents('tr').show();
        }else{
           $("#age3-direction").parents('tr').hide();
        }
        });

        //newsletter js

        $("#subscribe-newsletter-form").on('submit',function(e){
        e.preventDefault();
        var $form = $("#subscribe-newsletter-form");
        var name = $form.find('input[name="name"]').val();
        var email = $form.find('input[name="email"]').val();
        var website = $form.find('input[name="company"]').val();
        $.post(ajaxurl, {action:'suprp_subscribe_newsletter',name:name, email:email,website:website,srp_security_nonce:srp_localize_data.srp_security_nonce},
          function(data) {}
        );
    });
            
    // Code for support tab
    $(".srpp-send-query").on("click", function(e){
        e.preventDefault();   
        var message     = $("#srpp_query_message").val();  
        var email       = $("#srpp_query_email").val();
        
        if($.trim(message) !='' && $.trim(email) !='' && srppIsEmail(email) == true){
         $.ajax({
                        type: "POST",    
                        url:ajaxurl,                    
                        dataType: "json",
                        data:{action:"srpp_send_query_message", message:message,email:email,srp_security_nonce:srp_localize_data.srp_security_nonce},
                        success:function(response){                       
                          if(response['status'] =='t'){
                            $(".srpp-query-success").show();
                            $(".srpp-query-error").hide();
                          }else{                                  
                            $(".srpp-query-success").hide();  
                            $(".srpp-query-error").show();
                          }
                        },
                        error: function(response){                    
                        console.log(response);
                        }
                        });   
        }else{
            
            if($.trim(message) =='' &&  $.trim(email) ==''){
                alert('Please enter the message and email');
            }else{
            if($.trim(message) == ''){
                alert('Please enter the message');
            }
            if($.trim(email) == ''){
                alert('Please enter the email');
            }
            if(srppIsEmail(email) == false){
                alert('Please enter a valid email');
            }
                
            }
            
        }                        

    });

        /* Newletters js starts here */      
            
        if(srp_localize_data.do_tour){
            flag = 0;
            var  content = '<h3>Thanks for using Super Related Posts!</h3>';
                 content += '<p>Do you want the latest updates on <b>Super Related Posts update</b> before others and some best resources on monetization in a single email? - Free just for users of Super Related Posts!</p>';
                 content += '<style type="text/css">';
                 content += '.wp-pointer-buttons{ padding:0; overflow: hidden; }';
                 content += '.wp-pointer-content .button-secondary{  left: -25px;background: transparent;top: 5px; border: 0;position: relative; padding: 0; box-shadow: none;margin: 0;color: #0085ba;} .wp-pointer-content .button-primary{ display:none}  #srpwp_mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; }';
                 content += '</style>';                        
                 content += '<div id="srpwp_mc_embed_signup">';
                 content += '<form method="POST" accept-charset="utf-8" id="srpwp-news-letter-form">';
                 content += '<div id="srpwp_mc_embed_signup_scroll">';
                 content += '<div class="srpwp-mc-field-group" style="    margin-left: 15px;    width: 195px;    float: left;">';
                 content += '<input type="text" name="srpwp_subscriber_name" class="form-control" placeholder="Name" hidden value="'+srp_localize_data.current_user_name+'" style="display:none">';
                 content += '<input type="text" value="'+srp_localize_data.current_user_email+'" id="srpwp_subscriber_email" name="srpwp_subscriber_email" class="form-control" placeholder="Email*"  style="      width: 180px;    padding: 6px 5px;">';                        
                 content += '<input type="text" name="srpwp_subscriber_website" class="form-control" placeholder="Website" hidden style=" display:none; width: 168px; padding: 6px 5px;" value="'+srp_localize_data.get_home_url+'">';
                 content += '<input type="hidden" name="ml-submit" value="1" />';
                 content += '</div>';
                 content += '<div id="mce-responses">';                                                
                 content += '</div>';
                 content += '<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_a631df13442f19caede5a5baf_c9a71edce6" tabindex="-1" value=""></div>';
                 content += '<input type="submit" value="Subscribe" name="subscribe" id="pointer-close" class="button mc-newsletter-sent" style=" background: #0085ba; border-color: #006799; padding: 0px 16px; text-shadow: 0 -1px 1px #006799,1px 0 1px #006799,0 1px 1px #006799,-1px 0 1px #006799; height: 30px; margin-top: 1px; color: #fff; box-shadow: 0 1px 0 #006799;">';
                 content += '<p id="srpwp-news-letter-status"></p>';
                 content += '</div>';
                 content += '</form>';
                 content += '</div>';
                 
                 $(document).on("submit", "#srpwp-news-letter-form", function(e){
                   e.preventDefault(); 
                   
                   var $form = $(this),
                   name = $form.find('input[name="srpwp_subscriber_name"]').val(),
                   email = $form.find('input[name="srpwp_subscriber_email"]').val();
                   website = $form.find('input[name="srpwp_subscriber_website"]').val();

                    if($.trim(email) !='' && srppIsEmail(email) == true){    
                        flag = 1;                    
                       $.post(srp_localize_data.ajax_url,
                                  {action:'srp_subscribe_to_news_letter',
                                  saswp_security_nonce:srp_localize_data.srpwp_security_nonce,
                                  name:name, email:email, website:website },
                         function(data) {
                           
                             if(data)
                             {
                               if(data=="Some fields are missing.")
                               {
                                 $("#srpwp-news-letter-status").text("");
                                 $("#srpp-news-letter-status").css("color", "red");
                               }
                               else if(data=="Invalid email address.")
                               {
                                 $("#srpwp-news-letter-status").text("");
                                 $("#srpwp-news-letter-status").css("color", "red");
                               }
                               else if(data=="Invalid list ID.")
                               {
                                 $("#srpwp-news-letter-status").text("");
                                 $("#srpwp-news-letter-status").css("color", "red");
                               }
                               else if(data=="Already subscribed.")
                               {
                                 $("#srpwp-news-letter-status").text("");
                                 $("#srpwp-news-letter-status").css("color", "red");
                               }
                               else
                               {
                                 $("#srpwp-news-letter-status").text("You're subscribed!");
                                 $("#srpwp-news-letter-status").css("color", "green");
                               }
                             }
                             else
                             {
                               alert("Sorry, unable to subscribe. Please try again later!");
                             }
                         }
                       );
                    }else{
                        $('#srpwp_subscriber_email').focus();
                        $('#srpwp-news-letter-status').html('<strong style="color: #FF0000">Please enter valid email id</strong>');
                        return false;
                    }
                 });

         
         var setup;                
         var wp_pointers_tour_opts = {
             content:content,
             position:{
                 edge:"top",
                 align:"left"
             }
         };
                         
         wp_pointers_tour_opts = $.extend (wp_pointers_tour_opts, {
                 buttons: function (event, t) {
                         button= jQuery ('<a id="pointer-close" class="button-secondary">' + srp_localize_data.button1 + '</a>');
                         button_2= jQuery ('#pointer-close.button');
                         button.bind ('click.pointer', function () {
                            t.element.pointer ('close');
                         });
                         button_2.on('click', function() {
                           setTimeout(function(){ 
                               if(flag == 1){
                                t.element.pointer ('close');
                               }
                          }, 3000);
                               
                         } );
                         return button;
                 },
                 close: function () {
                         $.post (srp_localize_data.ajax_url, {
                                 pointer: 'srpwp_subscribe_pointer',
                                 action: 'dismiss-wp-pointer'
                         });
                 },
                 show: function(event, t){
                  t.pointer.css({'left':'170px', 'top':'160px'});
               }                                               
         });
         setup = function () {
                 $('.settings_page_super-related-posts').pointer(wp_pointers_tour_opts).pointer('open');
                  // if (srp_localize_data.button2) {
                         jQuery ('#pointer-close').after ('<a id="pointer-primary" class="button-primary">' + srp_localize_data.button2+ '</a>');
                         jQuery ('#pointer-primary').click (function () {
                                 srp_localize_data.function_name;
                         });
                         jQuery(document).on('click', '#pointer-close', function(e){
                                 $.post (srp_localize_data.ajax_url, {
                                         pointer: 'srpwp_subscribe_pointer',
                                         action: 'dismiss-wp-pointer'
                                 });
                         });
                  // }
         };
         if (wp_pointers_tour_opts.position && wp_pointers_tour_opts.position.defer_loading) {
                 $(window).bind('load.wp-pointers', setup);
         }
         else {
                 setup ();
         }
         
     }
         
    /* Newletters js ends here */ 

    /* Display super related options */
     $(document).on('change', '.srpp-display-status', function(e){
        if($(this).is(':checked')){
            $('.srpp-parent-options').show();
            $('.suprp-design-img').show();
            if($('.srpwp-adv-filter-check').is(':checked')){
                $("#filter_options tr").show();    
            }

        }else{
            $('.srp-optiontable tr').hide();
            $('.suprp-design-img').hide();
            $("#filter_options tr").hide();
            $('.srp-optiontable tr:first').show();
        }
     });
     /* Display super related options ends here */

     if($('.srpp-display-status').length > 0){
        if($('.srpp-display-status').is(':checked')){
            $('.srpp-parent-options').show();
            if($('.srpwp-adv-filter-check').is(':checked')){
                $("#filter_options tr").show();    
            }   
        }else{
             $('.srp-optiontable tr').hide();
             $('.srp-optiontable tr:first').show();
             $('.suprp-design-img').hide();
             $("#filter_options tr").hide();
        }
     }
});

function srppIsEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}