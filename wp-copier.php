<?php
/**
 * @link    http://mkhossain.com/development/plugins/wp-copier
 * @package WP Copier
 * @since   1.0.0
 * @version 1.0.0
 * 
 * @wordpress-plugin
 * Plugin Name: WP Copier
 * Plugin URI: http://mkhossain.com/development/plugins/wp-copier
 * Description: This is just a plugin to import posts and pages from remote url wordpress website.
 * Author: MD Mustafa Kamal Hossain	
 * Version: 1.0.0
 * Author URI: http://mkhossain.com
 * Text Domain: wp_copier
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WP_COPIER_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/activator.php
 */
function activate_wp_copier() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/activator.php';
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/deactivator.php
 */
function deactivate_wp_copier() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/deactivator.php';
}

register_activation_hook( __FILE__, 'activate_wp_copier' );
register_deactivation_hook( __FILE__, 'deactivate_wp_copier' );



/**
 * Register a custom menu page.
 */
function wp_copier_add_settings_page() {
    add_menu_page(
        __( 'WP Copier', 'wp_copier' ),
        'WP Copier Settings',
        'manage_options',
        'wp-copier/options-settings.php',
        'add_settings_page',
        plugins_url( 'wp-copier/images/icon.png' ),
        6
    );
}
add_action( 'admin_menu', 'wp_copier_add_settings_page' );


function add_settings_page(){
  require_once('admin/gui/settings.php');
}


add_action( 'rest_api_init', 'custom_api_get_all_posts' );   

function custom_api_get_all_posts() {
    register_rest_route( 'custom/v1', '/all-posts', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_all_posts_callback',
        'permission_callback' => '__return_true',
    ));
}

function custom_api_get_all_posts_callback( $request ) {
    // Initialize the array that will receive the posts' data. 
    $posts_data = array();
    // Receive and set the page parameter from the $request for pagination purposes
    $paged = $request->get_param( 'page' );
    $paged = ( isset( $paged ) || ! ( empty( $paged ) ) ) ? $paged : 1; 
    $postType = ($request->get_param('post_type'))? $request->get_param('post_type') : 0;
    // Get the posts using the 'post' and 'news' post types

    if(! $postType){
      $allPostTypes = get_post_types();
      unset($allPostTypes['revision']);
      unset($allPostTypes['nav_menu_item']);
      unset($allPostTypes['custom_css']);
      unset($allPostTypes['customize_changeset']);
      unset($allPostTypes['oembed_cache']);
      unset($allPostTypes['user_request']);
    }else{
      $allPostTypes = array($postType);
    }
    
    $posts = get_posts( array(
            'paged' => $paged,
            'post__not_in' => get_option( 'sticky_posts' ),
            'posts_per_page' => -1,            
            'post_type' => array_values($allPostTypes) // This is the line that allows to fetch multiple post types. 
        )
    ); 
    // Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
    foreach( $posts as $post ) {
        $id = $post->ID; 
        $post_thumbnail = ( has_post_thumbnail( $id ) ) ? get_the_post_thumbnail_url( $id ) : null;

        $posts_data[] = (object) array( 
            'post' => get_post($id, 'ARRAY_A'), 
            'post_meta' => get_post_meta($id), 
            'featured_img_src' => $post_thumbnail,
            'upload_dir' => wp_upload_dir()
        );
    }                  
    return $posts_data;                   
} 

/**
 * save image and files from main server to duplicate server
 * Check if the image is already exist 
 * @param args array 
 * @return item array url and id(optional) 
 */

function save_attachement( $args = [
  'url'=> null,
  'mainServerLocation' => null,
  'id' => null
]){
  
  $upload_dir = wp_upload_dir();
  $image_data = file_get_contents( $args['url'] );
  $filename = basename( $args['url'] );
  
  $uploadNeeded = true;

  $thisImagePath = str_replace($args['mainServerLocation']->baseurl, '', $args['url']);
  if(file_exists($upload_dir['basedir'] . '/' . $thisImagePath )){
    return array(
      'url' => $upload_dir['baseurl'] . $thisImagePath ,
      'id' => (array_key_exists('id', $args))? $args['id'] : null
    );
  }else{
    if ( wp_mkdir_p( $upload_dir['path'] ) ) {
      $file = $upload_dir['path'] . '/' . $filename;
    }
    else {
      $file = $upload_dir['basedir'] . '/' . $filename;
    }
  
    file_put_contents( $file, $image_data );
  
    $wp_filetype = wp_check_filetype( $filename, null );
  
    $attachment = array(
      'post_mime_type' => $wp_filetype['type'],
      'post_title' => sanitize_file_name( $filename ),
      'post_content' => '',
      'post_status' => 'inherit'
    );
  
    $attach_id = wp_insert_attachment( $attachment, $file );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    $attachmentIS = wp_update_attachment_metadata( $attach_id, $attach_data );
    return array(
      'url' => null,
      'id' => $attach_id
    ); 
  }

 }

/**
 * Process meta data 
 * @param $meta array
 * @return Array of processed data
 */

 function processMetadatas( $meta, $upload_dir ){
  if(is_array($meta)){
    foreach ($meta as $key => $value){
      $meta[$key] = processMetadatas($value, $upload_dir);
    }
  }else{
    $supported_image = array( 'gif', 'jpg', 'jpeg', 'png' );
    
    $ext = explode('.', $meta);
    if(in_array(end($ext), $supported_image)){
      $attachmentDetails = save_attachement( array('url' => $meta, 'mainServerLocation' => $upload_dir ) );
      if( $attachmentDetails['url'] ){
        $meta = $attachmentDetails['url'];
      }else{
        $meta = wp_get_attachment_url($attachmentDetails['id']);
      }
    }
  }
  return $meta;
 }