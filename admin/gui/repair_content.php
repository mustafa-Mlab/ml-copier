<?php 

$services = get_posts( array(
  'posts_per_page' => -1,
  'post_type' => 'ml-services',
  'fields' => 'ids'
) );

foreach($services as $key => $value ){
  $serviceMeta = get_post_meta($value, 'service_post_meta', true);
  foreach($serviceMeta as $index => $data){

    // ml-service-cases-tier-one
    // ml-service-blog-tier-one
    // ml-service-related-service-tier-one
    // ml-service-cases-tier-two
    // ml-service-blog-tier-two


    if( $index === 'ml-service-cases-tier-one' || $index === 'ml-service-blog-tier-one' || $index === 'ml-service-related-service-tier-one' || $index === 'ml-service-cases-tier-two' || $index === 'ml-service-blog-tier-two' ) 
    {
      if(is_array($data) ){
        foreach($data as $metaIndexKey => $metaIndexData){
         if(get_post($metaIndexData) === NULL){
           unset($data[$metaIndexKey]);
          }
        }
      }
    }
    $serviceMeta[$index] = $data;
  }
  update_post_meta($value, 'service_post_meta', $serviceMeta );
}