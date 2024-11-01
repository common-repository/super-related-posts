<?php

/**
 * Helper Functions
 *
 * @package     saswp
 * @subpackage  Helper/Templates
 * @copyright   Copyright (c) 2016, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

/**
 * Helper method to check if user is in the plugins page.
 *
 * @author René Hermenau
 * @since  1.4.0
 *
 * @return bool
 */

/**
 * display deactivation logic on plugins page
 * 
 * @since 1.4.0
 */

add_filter('admin_footer', 'suprp_add_deactivation_feedback_modal');
function suprp_add_deactivation_feedback_modal() {

    if( !is_admin()) {
        return;
    }

    $current_user = wp_get_current_user();
    if( !($current_user instanceof WP_User) ) {
        $email = '';
    } else {
        $email = trim( $current_user->user_email );
    }
    require_once SRPP_DIR_NAME ."/includes/deactivate-feedback.php";
}

/**
 * send feedback via email
 * 
 * @since 1.4.0
 */
function suprp_send_feedback() {

    if( isset( $_POST['data'] ) ) {
        parse_str( $_POST['data'], $form );
    }
    if(!current_user_can('manage_options')){
        die('-1');
    }
    if ( ! isset( $form['srp_feedback_nonce'] ) ){
       die('-1'); 
    }
    if ( !wp_verify_nonce($form['srp_feedback_nonce'], 'srp_feedback_check_nonce' ) ){
       die('-1');
    }

    $text = '';
    if( isset( $form['suprp_disable_text'] ) ) {
        $text = implode( "\n\r", $form['suprp_disable_text'] );
    }

    $headers = array();

    $from = isset( $form['suprp_disable_from'] ) ? $form['suprp_disable_from'] : '';
    if( $from ) {
        $headers[] = "From: $from";
        $headers[] = "Reply-To: $from";
    }

    $subject = isset( $form['suprp_disable_reason'] ) ? $form['suprp_disable_reason'] : '(no reason given)';

    if($subject == 'technical issue'){

          $text = trim($text);

          if(!empty($text)){

            $text = 'technical issue description: '.$text;

          }else{

            $text = 'no description: '.$text;
          }

    }

    $success = wp_mail( 'team@magazine3.in', $subject, $text, $headers );

    die();
}
add_action( 'wp_ajax_suprp_send_feedback', 'suprp_send_feedback' );

function suprp_enqueue_makebetter_email_js(){

    if( !is_admin() ) {
        return;
    }

    wp_enqueue_script( 'suprp-make-better-js', SRPP_PLUGIN_URI . 'includes/feedback.js', array( 'jquery' ));

    wp_enqueue_style( 'suprp-make-better-css', SRPP_PLUGIN_URI . 'css/feedback.css', false  );
}
add_action( 'admin_enqueue_scripts', 'suprp_enqueue_makebetter_email_js' );



add_action('wp_ajax_suprp_subscribe_newsletter','suprp_subscribe_for_newsletter');
function suprp_subscribe_for_newsletter(){
    if(!current_user_can('manage_options')){
        die('-1');
    }
    if ( ! isset( $_POST['srp_security_nonce'] ) ){
        die('-1'); 
    }
    if ( !wp_verify_nonce( $_POST['srp_security_nonce'], 'srp_ajax_check_nonce' ) ){
        die('-1'); 
    }
    $api_url = 'http://magazine3.company/wp-json/api/central/email/subscribe';
    $api_params = array(
        'name' => sanitize_text_field($_POST['name']),
        'email'=> sanitize_text_field($_POST['email']),
        'website'=> sanitize_text_field($_POST['website']),
        'type'=> 'suprp'
    );
    $response = wp_remote_post( $api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
    $response = wp_remote_retrieve_body( $response );
    echo $response;
    die;
} 