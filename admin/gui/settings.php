<?php 
$url = isset($_SESSION['url'])? $_SESSION['url'] : '';
$flag = false;
$copyFlag = false;
$list = [];
if(isset($_POST['list'])){
  if( isset($_POST['url']) && ! empty($_POST['url']) ){
    $_SESSION['url'] = rtrim($_POST['url'], "/");
    $url = $_SESSION['url'];
    if(!isset($_SESSION['list']) || $_SESSION['url'] != $url )
    $_SESSION['list'] = json_decode(file_get_contents($url . '/wp-json/custom/v1/all-posts-types'));
    $list = $_SESSION['list'];
    $flag = 1;
  }else{
    $flag = 0;
  } 
}

$checkboxArray = [];
$taxArray = [];
  if( isset($_POST['submit']) && isset($_POST['posts'])){

    $postIDArray = $_POST['posts'];
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
                    $thisInnerArrayMetaValue[$metaValuename] = processMetadatas($metaValueData, $data->upload_dir);
                    
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










    // $copyFlag = 1;
    //  echo '<pre>'; var_dump($_POST); echo '</pre>'; die();
    // if( isset($_POST['checkboxArray']) && ! empty($_POST['checkboxArray'])){
    //   $checkboxArray = $_POST['checkboxArray'];
    // }else{
    //   $copyFlag = 0;
    // }
    // $limit = (isset($_POST['limit']) && ! empty($_POST['limit']))? $_POST['limit'] : -1;
    // $orderBy = (isset($_POST['order-by']) && ! empty($_POST['order-by']))? $_POST['order-by'] : 'none';
    // $order = (isset($_POST['order']) && ! empty($_POST['order']))? $_POST['order'] : 'ASC';

    // if($copyFlag){
    //   unset($_SESSION['list']);
    //   foreach( $checkboxArray as $key => $value){
    //     $posts = json_decode(file_get_contents($url . "/wp-json/custom/v1/all-posts?post_type={$value}" ));
    //     // &limit={$limit}&order-by={$orderBy}&order={$order}
    //     foreach($posts as $index => $data){
    //       if(! post_exists($data->post->post_title, '', '', $value)){
    //         // Create the post first
    //         $thisID = $data->post->ID;
    //         foreach($data->post as $itemKey => $itemValue){
    //           $postArray[$itemKey] = $itemValue; 
    //         }
    //         unset($postArray['ID']); // Wordpress will assign a new Id
    //         unset($postArray['guid']); // Wordpress will add new guid

    //         /** upload respactive image inside post content */
    //         $postContent = $postArray['post_content'];
    //         preg_match_all('/<img[^>]+>/i',$postContent, $result); 
    //         $img = array();
    //         foreach( $result as $implementedResult){
    //           foreach( $implementedResult as $img_tag){
    //             preg_match_all('/(src=")([^"]*)"/i',$img_tag, $img[$img_tag]);
    //           }
    //         }
    //         $processedImg = array();
    //         foreach($img as $imgKey => $imgTag){
    //           $attachmentDetails = save_attachement( 
    //             array( 'url' => preg_replace('/(-[0-9]*x[0-9]*)/m', '', end($imgTag)[0]), 'mainServerLocation' => $data->upload_dir ) 
    //           );
    //           if($attachmentDetails['id']){
    //             $processedImg[end($imgTag)[0]] = $attachmentDetails['id'];
    //           }else{
    //             $processedImg[end($imgTag)[0]] = $attachmentDetails['url'];
    //           }
    //         }
    //         /** upload respactive image inside post content */
            
    //         $postArray['post_author'] = get_current_user_id(); // Current user will be the author of this post
    //         $newID = wp_insert_post($postArray); // Inserting new post 

    //         $terms = $data->terms;
            
    //         foreach ($terms as $objectArrayKey => $objectArrayValue){
    //           foreach($objectArrayValue as $key => $value){
    //             if(term_exists( $value->slug, $value->taxonomy )){
    //               $thisTerm = term_exists( $value->slug, $value->taxonomy );
    //             }else{
    //               $thisTerm = wp_insert_term($value->name, $value->taxonomy, array('description' => $value->description, 'parent' => $value->parent, 'slug' => $value->slug  ) );
    //             }
    //             wp_set_object_terms( $newID, (int)$thisTerm['term_id'], $value->taxonomy );
    //           }
    //         }

            
    //         // Now we have new post Id in $newID lets add all metas
    //         $postMeta = $data->post_meta;
    //         foreach($postMeta as $postMetaKey => $postMetaValue){

    //           // Lets replace the thumbnil or feature image first: 
    //           if($postMetaKey === '_thumbnail_id'){
    //             // get the thumbnil image src / url 
                
    //             $attachmentDetails = save_attachement( 
    //               array( 'url' => $data->featured_img_src, 'id' => $postMetaValue[0], 'mainServerLocation' => $data->upload_dir ) 
    //             );
    //             add_post_meta($newID, $postMetaKey, $attachmentDetails['id']);
    //           } 

    //           // Now change the codestars meta boxs image links
    //           else{
    //             // By deafult all the meta are repreasnting by array
    //             if(is_array($postMetaValue)){
                  
    //               foreach($postMetaValue as $innerArrayMetaValueKey => $innerArrayMetaValue ){
    //                 /** wordprress deafult metas will be theonly entry inside the array
    //                   * but codestars meta could have multiple entry
    //                   * in this loop all the meta will extract one by one inside the array, and will check if the value is serialized
    //                   * If value is serialized then it is the value from codestrs framework
    //                   * if we get any serialized value we will unseralize theme and loop each content for image or media url
    //                   * if we find any we will replace the link and ID then serialize all data again and push to post meta
    //                 */
    //                 if(is_serialized( $innerArrayMetaValue )){
    //                   $thisInnerArrayMetaValue = unserialize($innerArrayMetaValue);
    //                   foreach($thisInnerArrayMetaValue as $metaValuename => $metaValueData){
    //                     $thisInnerArrayMetaValue[$metaValuename] = processMetadatas($metaValueData, $data->upload_dir);
                        
    //                   }
    //                   add_post_meta($newID, $postMetaKey, $thisInnerArrayMetaValue);
    //                 }else{
    //                   // If not serialized data
    //                   add_post_meta($newID, $postMetaKey, $innerArrayMetaValue);
    //                 }
    //               }
    //             }
    //           }
              
    //           // $thisItemValue[$thisIndex] = str_replace($url, $_SERVER['HTTP_ORIGIN'], $thisValue);
    //         } 
    //       }
    //     }
    //   }
    // }
  }
  if( ! $flag && ! $copyFlag){
    unset($_SESSION['url']);
    unset($_SESSION['list']);
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
  }  ?>

  <form action="#" name="list_form" method="post">
  <label for="url">Main Site URL</label>
  <input type="text" name="url" id="url" value="<?= $url;?>" required>
  <br>
  <br>
  <input type="submit" value="Get Post Types List" name="list" >
  </form>
  <br>
  <br>
  <?php if( isset($_SESSION['list'])){  ?>

    <form action="#" name="wp_copier_form" id="wp_copier_form" method="post">
    <h2>Post Types</h2>
    <?php 
      $postTypes = $_SESSION['list']->post_types;
      unset($postTypes->revision);
      unset($postTypes->nav_menu_item);
      unset($postTypes->custom_css);
      unset($postTypes->customize_changeset);
      unset($postTypes->oembed_cache);
      unset($postTypes->user_request);
      unset($postTypes->attachment);
      foreach($postTypes as $key => $value){
    ?>
      <div class="postTyperow">
        <input type="checkbox" name="checkboxArray[]" class="postTypeInputBox" value="<?= $value; ?>" <?= ( $flag && in_array($value , $checkboxArray)) ? 'checked' :''; ?>>
        <label> <?= ucfirst($value); ?></label><br>
        <div class="fetch-data">
          <div class="all-section">
            <input type="checkbox" name="get-all" id="get-all-<?= $value;?>" class="get-all" checked>
            <label for="get-all">Scrab All</label>
          </div>
          <div class="id-specific-section">
            <ul class="id-specific-posts">
            </ul>
          </div>
        </div>
      </div>
      <?php  } ?>
    
    <br> <br>
    <?php /**
    <hr>
    <h2>Taxonomies</h2>
    <?php 
      $taxonomies = $_SESSION['list']->taxonomies;
      unset($taxonomies->nav_menu);
      unset($taxonomies->link_category);
      unset($taxonomies->post_format);
      foreach($taxonomies as $key => $value){
    ?>
        <input type="checkbox" name="taxArray[]" value="<?= $value->name; ?>" <?= ( $flag && in_array($value->name , $taxArray)) ? 'checked' :''; ?>>
        <label> <?= ucfirst($value->label .' --> ' . str_replace('"', '', json_encode( implode(',', $value->object_type)))); ?></label><br>
      <?php  } ?>
    
    <br> <br>
    <hr>
    <label for="limit">Limit</label>
    <input type="number" name="limit" id="limit" value="<?= ( isset( $_POST['limit'] ) )? $_POST['limit'] : "" ; ?>">
    <br><br>
    
    <hr>
    <label for="order-by">Order BY</label>
    <select name="order-by" id="order-by" >
      <option value="none" selected>None</option>
      <option value="ID" >ID</option>
      <option value="author" >Author</option>
      <option value="title">Title</option>
      <option value="name">Name</option>
      <option value="date">Date</option>
      <option value="modified">Modified</option>
      <option value="rand">Rand</option>
    </select>
    <br><br>
    <hr>
    <label for="order">Order</label>
    <select name="order" id="order" >
      <option value="ASC">Ascending</option>
      <option value="DESC">Descending</option>
    </select>
    <br><br>
     */ ?>
    <input type="submit" value="Start Copy" name="submit" style="position: fixed; bottom: 50px; right: 2%;">
  </form>
  <?php }  ?>
  </div>
</div>

<?php 

