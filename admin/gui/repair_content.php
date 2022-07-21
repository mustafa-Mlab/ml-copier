<?php 
// Services repaire
// $services = get_posts( array(
//   'posts_per_page' => -1,
//   'post_type' => 'ml-services',
//   'fields' => 'ids'
// ) );

// foreach($services as $key => $value ){
//   $serviceMeta = get_post_meta($value, 'service_post_meta', true);
//   foreach($serviceMeta as $index => $data){
//     if( $index === 'ml-service-cases-tier-one' || $index === 'ml-service-blog-tier-one' || $index === 'ml-service-related-service-tier-one' || $index === 'ml-service-cases-tier-two' || $index === 'ml-service-blog-tier-two' ) 
//     {
//       if(is_array($data) ){
//         foreach($data as $metaIndexKey => $metaIndexData){
//           $thisService = get_post($metaIndexData);
//           if( $thisService === NULL || $thisService->post_status == 'publish'){
//            unset($data[$metaIndexKey]);
//           }
//         }
//       }
//     }
//     $serviceMeta[$index] = $data;
//   }
//   update_post_meta($value, 'service_post_meta', $serviceMeta );
// }

// Case repaire
// $cases = get_posts( array(
//   'posts_per_page' => -1,
//   'post_type' => 'ml-cases',
//   'fields' => 'ids'
// ) );

// foreach($cases as $key => $value ){
//   $caseMeta = get_post_meta($value, 'case_rich_post_meta', true);
//   foreach($caseMeta as $index => $data){
//     if( $index === 'related-cases' || $index === 'rich-case-selected' ) 
//     {
//       if(is_array($data) ){
//         foreach($data as $metaIndexKey => $metaIndexData){
//           $thisCase = get_post($metaIndexData);
//           if( $thisCase === NULL || $thisCase->post_status == 'publish'){
//            unset($data[$metaIndexKey]);
//           }
//         }
//       }
//     }
//     $caseMeta[$index] = $data;
//   }
//   update_post_meta($value, 'case_rich_post_meta', $caseMeta );
// }

// Case repaire
// $cases = get_posts( array(
//   'posts_per_page' => -1,
//   'post_type' => 'ml-cases',
//   'fields' => 'ids'
// ) );

// foreach($cases as $key => $value ){
//   $caseMeta = get_post_meta($value, 'case_rich_post_meta', true);
//   foreach($caseMeta as $index => $data){
//     if( $index === 'related-cases' || $index === 'rich-case-selected' ) 
//     {
//       if(is_array($data) ){
//         foreach($data as $metaIndexKey => $metaIndexData){
//           $thisCase = get_post($metaIndexData);
//           if( $thisCase === NULL || $thisCase->post_status == 'publish'){
//            unset($data[$metaIndexKey]);
//           }
//         }
//       }
//     }
//     $caseMeta[$index] = $data;
//   }
//   update_post_meta($value, 'case_rich_post_meta', $caseMeta );
// }


repaireContent();