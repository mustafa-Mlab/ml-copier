<?php 
$flag = false;
$url  = (isset($_POST['url']))? $_POST['url'] : '';
$post_ids = (isset($_POST['posts_id']))? $_POST['posts_id'] : '';
if( isset($_POST['submit'] )){
  if(!empty($_POST['url']) && !empty($_POST['posts_id']) ){
    $flag = 1;
    $postIDArray = explode(',', $post_ids);
    foreach($postIDArray as $postArrayKey => $postArrayValue){
      $posts = json_decode(file_get_contents($url . "/wp-json/custom/v1/my-posts?key={$postArrayValue}" ));
      if(! post_exists($posts[0]->post->post_title, '', '', $posts[0]->post_type)){
        // Create the post first
        $data = $posts[0];
        $thisID = $data->post->ID;
        foreach($data->post as $itemKey => $itemValue){
          $postArray[$itemKey] = $itemValue; 
        }
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
          
          // $thisItemValue[$thisIndex] = str_replace($url, $_SERVER['HTTP_ORIGIN'], $thisValue);
        } 
      }


    }
  }else{
    $flag = 2;
  }
}
?>

<div class="wrap">
  <h1 class="wp-heading-inline">Copy by ID</h1>
  <br><br>
  <hr class="wp-header-end">

  <div class="form">
  
  <?php if($flag == 2){
    echo '<div class="form-error">';
    echo "Please select at least one post type and input main site url ";
    echo '</div> <br><br>';
  }  ?>
    <form action="#" name="wp_copier_form" method="post">
        <input type="text" name="url" id="url" value="<?= $url;?>" placeholder="Main Site URL">
        <br><br>
        <input type="text" name="posts_id" id="posts_id" value="<?= $post_ids;?>" placeholder="Posts IDs (comma Separated)">
        <input type="submit" value="Start Copy" name="submit" style="position: fixed; bottom: 50px; right: 2%;">
    </form>

  </div>
</div>
