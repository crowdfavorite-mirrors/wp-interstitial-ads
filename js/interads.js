jQuery( function($){
	if(typeof(interAds) !== 'undefined' && interAds != null){
		//WAIT TIME
		var is_wait = interAds.is_wait;
		var is_cached = interAds.is_cached;
		if(is_wait){
			setTimeout(
			  function() 
			  {
				$('#interads').fadeIn('fast');
				interads_count();
			  }, (is_wait*1000));
		}
		if(!is_wait && !is_cached){
					interads_count();
		}
		
		//CACHED AD
		var is_cached = interAds.is_cached;
		if(is_cached){
		
			$.ajax({
				 type : "post",
				 dataType : "html",
				 cache: false,
				 url : interAds.ajaxurl,
				 data : {action: 'inter_ads_action', id_post : interAds.id_post },
				 success: function(response) {
					if(response.type != "" && response != "none_interads") {
					   $('body').append(response);
					   if(!is_wait){
					   		interads_count();
					   }
					}
					
				 }
			  });
		}
		
	}
});

function interads_count(){
	var min_txt = (typeof(interAds) !== 'undefined' && interAds != null && interAds.minutes) ? interAds.minutes : '';
	var sec_txt = (typeof(interAds) !== 'undefined' && interAds != null && interAds.seconds) ? interAds.seconds : '';

	jQuery(".interads-kkcount-down").kkcountdown({
                    minutesText	:  min_txt + ' : ',
                    secondsText	:  sec_txt,
                    displayZeroDays : false,
                    callback	: interads_close
                });
						
}

//Close Ad
function interads_close(){
  	jQuery('#interads').fadeOut('fast', function() {
    	jQuery('#interads').remove();
	});
}

