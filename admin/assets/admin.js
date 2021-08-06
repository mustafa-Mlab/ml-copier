"use strict";

(function($) {
  $(document).ready(function() {
    $('.postTypeInputBox').change(function(e){
      var checkbox = this;
      if(this.checked) {
        $(this).closest('.postTyperow').find('.fetch-data').show();
        var postType = $(this).val();
        var site = $("#url").val();
        var url = site.replace(/\/$/, "") + "/wp-json/custom/v1/all-posts-grab?post_type=" + postType;
        
        var thisPostTypeContainer = $(this).closest('.postTyperow').find('.id-specific-section .id-specific-posts');
        $.get(url, function(data, status){
          $(checkbox).closest('.postTyperow').find('.fetch-data label .post-counter').text(" (" + data.length + ")");
          $(data).each(function(index, value){
            // console.log(value);
            $(thisPostTypeContainer).append('<li><label><input type="checkbox" name="posts[]" class="posts" value="' + value.ID + '" checked >' + value.post_title + ' || Published : ' + value.post_date + ' || Modified: ' + value.post_modified + ' || Status: ' + value.post_status + ' </label></li> ');
          });
        });
    }else{
      $(this).closest('.postTyperow').find('.fetch-data').hide();
    }
    });

    $('.get-all').change( function(e){
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

    /**
     * make Ajax call
     * @value POST ID
     */
    function makeAjaxCall(postID){
      $.ajax({
        type: 'POST',
        url: ajax.ajaxurl,
        data: {
          'action' : "copySinglePost",
          'url' : $("#url").val(),
          'postID' : postID
        },
        success: function(response){
          $('.report .items-finished').append('<li>' + postID + ' ended copying as ' + response + ' </li>');
        },
        async:false
      });
    }
    // Make ajax requests

    $('#wp_copier_form').submit(function (e){
      e.preventDefault();
      
      const posts =  $('input[name="posts[]"]');
      const sleep = (ms) => {
        return new Promise((resolve) => setTimeout(resolve, ms));
      };
      const getNumFruit = (post) => {
        return sleep(1000).then((v) => {
          $('.report .items-started').append('<li>' + $(post).val() + ' started to copying</li>');
          makeAjaxCall($(post).val());
        });
      };
      const forLoop = async (_) => {
        $(".report").show();
        $('.report .status').text("Please do not close the browser tab, posts are started to copying");
        $('.report .loading').show();
        for (let index = 0; index < posts.length; index++) {
          const post = posts[index];
          await getNumFruit(post);
          // const numFruit = await getNumFruit(post);
          // console.log(numFruit);
        }
        $('.report .status-ends').text("All task is finished you can close the tab now");
        $('.report .loading').hide();
        // $(".report").hide();
      };
      forLoop();

    });
  })
})(jQuery);