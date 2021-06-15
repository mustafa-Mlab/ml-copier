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


 // Make sure we don't expose any info if called directly
if ( ! defined( 'ABSPATH' ) ) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
  exit;
}


// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

if ( ! defined( 'WP_COPIER_FILE' ) ) {
  define( 'WP_COPIER_FILE', __FILE__ );
}

if ( ! defined( 'WP_COPIER_PATH' ) ) {
  define( 'WP_COPIER_PATH', plugin_dir_path( WP_COPIER_FILE ));
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
    $submenu = [];
    $submenu[] = add_submenu_page(
      'wp-copier/options-settings.php',
      __( 'Copy by ID', 'wp_copier' ),
      __( 'Copy by ID', 'wp_copier' ),
      'manage_options',
      'wp-copier/copy_by_id.php',
      'add_ids_page',
      6
    );
    
    $submenu[] = add_submenu_page(
      'wp-copier/options-settings.php',
      __( 'Content refactor', 'wp_copier' ),
      __( 'Content refactor', 'wp_copier' ),
      'manage_options',
      'wp-copier/content_replair.php',
      'repair_content',
      7
    );
}
add_action( 'admin_menu', 'wp_copier_add_settings_page' );


function add_settings_page(){
  require_once('admin/gui/settings.php');
}


function add_ids_page(){
  require_once('admin/gui/add_ids_page.php');
}


function repair_content(){
  require_once('admin/gui/repair_content.php');
}


/**
 * Adding new rest endpoint to get data from the second server
 * There will be 3 API endpoints
 * 1. To get all the post_IDs for single or multiple post type with limit and order
 * 2. To get single post data by ID
 */
add_action( 'rest_api_init', 'custom_api_get_all_posts' );   

function custom_api_get_all_posts() {
  /**
   * Get all post types and taxonomies list
   */
  register_rest_route( 'custom/v1', '/all-posts-types', array(
    'methods' => 'GET',
    'callback' => 'custom_api_get_all_posts_types_callback',
    'permission_callback' => '__return_true',
  ));
  /** 
   * Get post types posts's id with post type assign terms
   */
    // register_rest_route( 'custom/v1', '/all-posts', array(
    //     'methods' => 'GET',
    //     'callback' => 'custom_api_get_all_posts_callback',
    //     'permission_callback' => '__return_true',
    // ));
  
   /** 
   * Get all posts id and title with url
   */
    register_rest_route( 'custom/v1', '/all-posts-grab', array(
      'methods' => 'GET',
      'callback' => 'custom_api_get_all_posts_grab_callback',
      'permission_callback' => '__return_true',
  ));

  /** 
   * Get post Data by id
   */
    register_rest_route( 'custom/v1', '/my-posts', array(
        'methods' => 'GET',
        'callback' => 'custom_api_get_posts_callback',
        'permission_callback' => '__return_true',
    ));
}

function custom_api_get_all_posts_types_callback($request){
  return ['post_types' =>  get_post_types(), 'taxonomies' => get_taxonomies('', 'objects')]; 
}

// function custom_api_get_all_posts_callback( $request ) {
//     // Initialize the array that will receive the posts' data. 
//     $posts_data = array();
//     // Receive and set the page parameter from the $request for pagination purposes
//     $paged = $request->get_param( 'page' );
//     $limit = $request->get_param( 'limit' );
//     $paged = ( isset( $paged ) || ! ( empty( $paged ) ) ) ? $paged : 1; 
//     $postType = ($request->get_param('post_type'))? $request->get_param('post_type') : 0;
//     // Get the posts using the 'post' and 'news' post types

//     if(! $postType){
//       $allPostTypes = get_post_types();
//       unset($allPostTypes['revision']);
//       unset($allPostTypes['nav_menu_item']);
//       unset($allPostTypes['custom_css']);
//       unset($allPostTypes['customize_changeset']);
//       unset($allPostTypes['oembed_cache']);
//       unset($allPostTypes['user_request']);
//     }else{
//       $allPostTypes = array($postType);
//     }

//     $terms = get_object_taxonomies( $allPostTypes );

//     /**
//      * order, limit, 
//      * Return array should be like [ post_type => [posts =>[single_posts], terms], upload_directory]
//      * So that we can access part by part
//      * query will have pagination and order 
//      */
    
//     $posts = get_posts( array(
//             // 'paged' => $paged,
//             'posts_per_page' => -1,            
//             'post_type' => array_values($allPostTypes), // This is the line that allows to fetch multiple post types.
//             'fields' => 'ids',
//         )
//     );
//     return $posts;
//     // Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
//     foreach( $posts as $post ) {
//         $id = $post->ID; 
//         $post_thumbnail = ( has_post_thumbnail( $id ) ) ? get_the_post_thumbnail_url( $id ) : null;

//         $taxData= [];
//         foreach($terms as $key => $value){
//           if( get_the_terms($id, $value) ){
//             $taxData[] = get_the_terms($id, $value);
//           }
//         }
//         $posts_data[] = (object) array( 
//             'post' => get_post($id, 'ARRAY_A'), 
//             'post_meta' => get_post_meta($id), 
//             'featured_img_src' => $post_thumbnail,
//             'upload_dir' => wp_upload_dir(),
//             // 'terms' => get_the_terms($id, $terms),
//             'terms' => $taxData,
//             // 'post-types' => $postType
//         );
//     }                 
//     return $posts_data;                   
// } 


function custom_api_get_all_posts_grab_callback( $request ){
  // Initialize the array that will receive the posts' data. 
  $posts = get_posts( array(
    'posts_per_page' => -1,            
    'post_type' => $request->get_param('post_type') // This is the line that allows to fetch multiple post types. 
  ));
             
  return $posts; 

}

function custom_api_get_posts_callback( $request ) {
    // Initialize the array that will receive the posts' data. 
    $posts_data = array();
    $id = $request->get_param('key');
    $post_type = get_post_type($id);
    if($post_type){
      $terms = get_object_taxonomies( $post_type );
      
      $post_thumbnail = ( has_post_thumbnail( $id ) ) ? get_the_post_thumbnail_url( $id ) : null;
  
      $taxData= [];
      foreach($terms as $key => $value){
        if( get_the_terms($id, $value) ){
          $taxData[] = get_the_terms($id, $value);
        }
      }
      $posts_data[] = (object) array( 
          'post' => get_post($id, 'ARRAY_A'), 
          'post_meta' => get_post_meta($id), 
          'featured_img_src' => $post_thumbnail,
          'upload_dir' => wp_upload_dir(),
          'terms' => $taxData,
          'post_type' => $post_type
      );             
      return $posts_data;                   
    }
    return 0;
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

 function processMetadatas( $meta, $upload_dir, $url ){
   if(is_array($meta)){
     foreach ($meta as $key => $value){
       $meta[$key] = processMetadatas($value, $upload_dir, $url);
      }
    }else{
      /**
       * $meta can be a string or integer or array
       * if the meta is array then it will be processed in first condition, so here it can not be array
       * if the meta is a string then type casting will return 0
       * if the meta is integer typecasting will return integer value 
       * if the meta is 0 then typecasting also return 0 
       * so we need to test that after type casting it will be anything but 0 or before type casting and after type casting it will return same
       */
      if((int)$meta !== 0 && (int)$meta !== 1 && strlen((int)$data) == strlen($data)){
        $thisPost = json_decode(file_get_contents($url . "/wp-json/custom/v1/my-posts?key={$meta}" ));
        if($thisPost === 0){
          return;
        }else{
          if(post_exists( $thisPost[0]->post->post_title)){
            return post_exists( $thisPost[0]->post->post_title);
          }else{
            return;
          }
        }
      }
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



 function register_my_session()
{
  if( !session_id() )
  {
    session_start();
  }
}

add_action('init', 'register_my_session');


/**
 * Enqueue a script in the WordPress admin on copier page.
 *
 * @param int $hook Hook suffix for the current admin page.
 */
function add_custom_css_to_admin( $hook ) {
  if ( 'toplevel_page_wp-copier/options-settings' == $hook || 'top-copier-settings_page_wp-copier/copy_by_id' == $hook ) {
    
    wp_enqueue_script( 'wp_copier_custom_js', plugin_dir_url( __FILE__ ) . 'admin/assets/admin.js', array('jquery'), '1.0' );
    wp_localize_script(
      'wp_copier_custom_js',
      'ajax',
      array(
          'ajaxurl' => admin_url('admin-ajax.php')
      )
    );
    wp_enqueue_style( 'wp_copier_custom_css', plugin_dir_url( __FILE__ ) . 'admin/assets/admin.css', '', '1');
  }
}
add_action( 'admin_enqueue_scripts', 'add_custom_css_to_admin' );

function copySinglePost(){
  $url = $_POST['url'];
  $postID = $_POST['postID'];
  $posts = json_decode(file_get_contents($url . "/wp-json/custom/v1/my-posts?key={$postID}" ));
  if(! post_exists($posts[0]->post->post_title, '', '', $posts[0]->post_type)){
    // Create the post first
    $data = $posts[0];
    $thisID = $data->post->ID;
    foreach($data->post as $itemKey => $itemValue){
      $postArray[$itemKey] = $itemValue; 
    }
    $previousID = $postArray['ID'];
    $previousURL = $postArray['guid'];

    unset($postArray['ID']); // Wordpress will assign a new Id
    unset($postArray['guid']); // Wordpress will add new guid

    /** upload respactive image inside post content */
    $postContent = $postArray['post_content'];
    preg_match_all('/<img[^>]+>/i',$postContent, $result); 
    $img = array();
    foreach( $result as $implementedResult){
      foreach( $implementedResult as $img_tag){
        preg_match_all('/(src=")([^"]*)"/i',$img_tag, $img[$img_tag]);
      }
    }
    
    $processedImg = array();
    foreach($img as $imgKey => $imgTag){
      $attachmentDetails = save_attachement( 
        array( 'url' => preg_replace('/(-[0-9]*x[0-9]*)/m', '', end($imgTag)[0]), 'mainServerLocation' => $data->upload_dir ) 
      );
      if($attachmentDetails['id']){
        $processedImg[end($imgTag)[0]] = $attachmentDetails['id'];
      }else{
        $processedImg[end($imgTag)[0]] = $attachmentDetails['url'];
      }
    }
    foreach($processedImg as $mainLink => $actualLink){
      $postContent = str_replace( $mainLink,$actualLink, $postContent);
    }
    $postArray['post_content'] = $postContent;
    /** upload respactive image inside post content */
    
    $postArray['post_author'] = get_current_user_id(); // Current user will be the author of this post
    $newID = wp_insert_post($postArray); // Inserting new post 

    $terms = $data->terms;
    
    foreach ($terms as $objectArrayKey => $objectArrayValue){
      foreach($objectArrayValue as $key => $value){
        if(term_exists( $value->slug, $value->taxonomy )){
          $thisTerm = term_exists( $value->slug, $value->taxonomy );
        }else{
          $thisTerm = wp_insert_term($value->name, $value->taxonomy, array('description' => $value->description, 'parent' => $value->parent, 'slug' => $value->slug  ) );
        }
        wp_set_object_terms( $newID, (int)$thisTerm['term_id'], $value->taxonomy );
      }
    }
    
    // Now we have new post Id in $newID lets add all metas
    add_post_meta( $newID, 'wp-copier', '1');
    add_post_meta( $newID, 'previous-id', $previousID);
    add_post_meta( $newID, 'previous-url', $previousURL);
    add_post_meta( $newID, 'copy-from', $url);

    $postMeta = $data->post_meta;
    foreach($postMeta as $postMetaKey => $postMetaValue){

      // Lets replace the thumbnil or feature image first: 
      if($postMetaKey === '_thumbnail_id'){ 
        // get the thumbnil image src / url 
        
        $attachmentDetails = save_attachement( 
          array( 'url' => $data->featured_img_src, 'id' => $postMetaValue[0], 'mainServerLocation' => $data->upload_dir ) 
        );
        add_post_meta($newID, $postMetaKey, $attachmentDetails['id']);
      } 

      // Now change the codestars meta boxs image links
      else{
        // By deafult all the meta are repreasnting by array
        if(is_array($postMetaValue)){
          
          foreach($postMetaValue as $innerArrayMetaValueKey => $innerArrayMetaValue ){
            /** wordprress deafult metas will be theonly entry inside the array
              * but codestars meta could have multiple entry
              * in this loop all the meta will extract one by one inside the array, and will check if the value is serialized
              * If value is serialized then it is the value from codestrs framework
              * if we get any serialized value we will unseralize theme and loop each content for image or media url
              * if we find any we will replace the link and ID then serialize all data again and push to post meta
            */
            if(is_serialized( $innerArrayMetaValue )){
              $thisInnerArrayMetaValue = unserialize($innerArrayMetaValue);
              foreach($thisInnerArrayMetaValue as $metaValuename => $metaValueData){
                $thisInnerArrayMetaValue[$metaValuename] = processMetadatas($metaValueData, $data->upload_dir, $url);
              }
              add_post_meta($newID, $postMetaKey, $thisInnerArrayMetaValue);
            }else{
              // If not serialized data
              add_post_meta($newID, $postMetaKey, $innerArrayMetaValue);
            }
          }
        }
      }
    } 
  }
  echo json_encode($newID) ;
}

add_action( 'wp_ajax_copySinglePost', 'copySinglePost' );
add_action( 'wp_ajax_nopriv_copySinglePost', 'copySinglePost' );
