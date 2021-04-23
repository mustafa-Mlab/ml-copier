"use strict";

(function($) {
  $(document).ready(function() {
    $('.postTypeInputBox').change(function(e){
      if(this.checked) {
        $(this).closest('.postTyperow').find('.fetch-data').show();
        var postType = $(this).val();
        var site = $("#url").val();
        var url = site.replace(/\/$/, "") + "/wp-json/custom/v1/all-posts-grab?post_type=" + postType;
        
        var thisPostTypeContainer = $(this).closest('.postTyperow').find('.id-specific-section .id-specific-posts');
        $.get(url, function(data, status){
          $(data).each(function(index, value){
            $(thisPostTypeContainer).append('<li><input type="checkbox" name="posts[]" value="' + value.ID + '" checked ><lable for="posts">' + value.post_title + '</lable></li> ');
          });
        });
    }else{
      $(this).closest('.postTyperow').find('.fetch-data').hide();
    }
    });

    $('.get-all').change( function(e){
      console.log(this);
      if(this.checked) {
        $(this).closest('.fetch-data').find('.id-specific-section').hide();
        $(this).closest('.fetch-data').find('.id-specific-section ul input:checkbox').each(function (index, value){
          $(value).prop('checked', true);
        })
      }else{
        $(this).closest('.fetch-data').find('.id-specific-section').show();
        $(this).closest('.fetch-data').find('.id-specific-section ul input:checkbox').each(function (index, value){
          $(value).prop('checked', false);
        })
      }
    });

  })
})(jQuery);