<?php 
$url = '';
$checkboxArray = [];
$flag = false;
  if( isset($_POST['submit'])){
    $flag = 1;
    if( isset($_POST['url']) && ! empty($_POST['url'])){
      $url = rtrim($_POST['url'], "/");
    }else{
      $flag = 0;
    }
    if( isset($_POST['checkboxArray']) && ! empty($_POST['checkboxArray'])){
      $checkboxArray = $_POST['checkboxArray'];
    }else{
      $flag = 0;
    }
    if($flag){
      foreach( $checkboxArray as $key => $value){
        $posts = json_decode(file_get_contents($url . '/wp-json/custom/v1/all-posts?post_type='. $value));
        foreach($posts as $index => $data){
          if(! post_exists($data->post->post_title, '', '', $value)){
            // Create the post first
            $thisID = $data->post->ID;
            foreach($data->post as $itemKey => $itemValue){
              $postArray[$itemKey] = $itemValue; 
            }
            unset($postArray['ID']);
            unset($postArray['guid']);
            $postArray['post_author'] = get_current_user_id();
            
            $newID = wp_insert_post($postArray); // Inserting new post 
            
            // Now we have new post Id in $newID lets add all metas
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
                        $supported_image = array( 'gif', 'jpg', 'jpeg', 'png' );
                      
                        $ext = explode('.', $metaValueData);
                        if(in_array(end($ext), $supported_image)){
                          $attachmentDetails = save_attachement( array('url' => $metaValueData, 'mainServerLocation' => $data->upload_dir ) );
                          if( $attachmentDetails['url'] ){
                            $thisInnerArrayMetaValue[$metaValuename] = $attachmentDetails['url'];
                          }else{
                            $thisInnerArrayMetaValue[$metaValuename] = wp_get_attachment_url($attachmentDetails['id']);
                          }
                        }
                      }
                      add_post_meta($newID, $postMetaKey, $thisInnerArrayMetaValue);
                    }else{
                      // If not serialized data
                      add_post_meta($newID, $postMetaKey, $innerArrayMetaValue);
                    }
                  }
                }
              }
              
              //     $thisItemValue[$thisIndex] = str_replace($url, $_SERVER['HTTP_ORIGIN'], $thisValue);
            } 
          }
        }
      }
    }
  }
?>

<div class="wrap">
  <h1 class="wp-heading-inline">WP Copier Settings</h1>
  <br><br>
  <hr class="wp-header-end">

  <div class="form">
  
  <?php if(!$flag){
    echo '<div class="form-error">';
    echo "Please select at least one post type and input main site url ";
    echo '</div> <br><br>';
  }
  ?>
  <form action="#" name="wp_copier_form" method="post">
  <label for="url">Main Site URL</label>
  <input type="text" name="url" id="url" value="<?= $url;?>" required>
  <br>
  <br>
  <?php 
  $allPostTypes = get_post_types();
  unset($allPostTypes['revision']);
  unset($allPostTypes['nav_menu_item']);
  unset($allPostTypes['custom_css']);
  unset($allPostTypes['customize_changeset']);
  unset($allPostTypes['oembed_cache']);
  unset($allPostTypes['user_request']);
  foreach($allPostTypes as $key => $value){
    ?>
    <input type="checkbox" name="checkboxArray[]" value="<?= $value; ?>" <?= ( $flag && in_array($value , $checkboxArray)) ? 'checked' :''; ?>>
    <label> <?= ucfirst($value); ?></label><br>
    <?php  } ?>
    <br> <br>
    <input type="submit" value="Start Copy" name="submit" style="position: fixed; bottom: 50px; right: 2%;">
  </form>
  </div>
</div>