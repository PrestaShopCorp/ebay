$(document).ready(function() {
    $('#ebay-seller-tips-link').click(function(event) {
      event.preventDefault();
      var sellerTips = $('#seller-tips');
      if (sellerTips.css('display') == 'none') {
          $(this).html(header_ebay_l['Hide seller tips']);
          sellerTips.show();
      } else {
          $(this).html(header_ebay_l['Show seller tips']);
          sellerTips.hide();                  
      }
      return false;
    });
  
  	$("#ebay_video_fancybox").click(function() {
  		$.fancybox({
  			'padding'		: 0,
  			'autoScale'		: false,
  			'transitionIn'	: 'none',
  			'transitionOut'	: 'none',
  			'title'			: this.title,
  			'width'			: 640,
  			'height'		: 385,
  			'href'			: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
  			'type'			: 'swf',
  			'swf'			: {
  			'wmode'				: 'transparent',
  			'allowfullscreen'	: 'true'
  			}
  		});

  		return false;
  	});

    $('.delete-profile').click(function(event) {
        event.preventDefault();
        var profileId = $(this).data('profile');
        if (confirm(header_ebay_l['Are you sure you want to delete the profile number %profile_number%?'].replace('%profile_number%', profileId))) {
            $.ajax({
                url: delete_profile_url + '&profile='+profileId,
                cache: false,
                success: function(data) {
                    location.reload();
                }
            });
        }
        return false;
    });
    
    function selectMainTab(menu_name) {
        
        $('.main-menu-a').parent().removeClass('selected');
        $('#' + menu_name + '-menu-link').parent().addClass('selected');
        
        $('.menuTab').hide();
        $('.menu-msg').hide();
        var menu = $('.' + menu_name + '-menu');
        $('.' + menu_name + '-menu').show();
        
        menu.children(":first").trigger('click');
        
    }
    
    $('.main-menu-a').click(function(event) {
        
        event.preventDefault();                
        
        var menuName = $(this).data('sub');
        
        selectMainTab(menuName);
        
        return false;
    });
    
    $(".menuTabButton").click(function () {
    		$(".menuTabButton.selected").removeClass("selected");
    		$(this).addClass("selected");
    		$(".tabItem.selected").removeClass("selected");
    		$("#" + this.id + "Sheet").addClass("selected");
    });

    selectMainTab(main_tab);

    if (id_tab) {
  		$(".menuTabButton.selected").removeClass("selected");
  		$("#menuTab" + id_tab).addClass("selected");
  		$(".tabItem.selected").removeClass("selected");
  		$("#menuTab" + id_tab + "Sheet").addClass("selected");      
    }

});
