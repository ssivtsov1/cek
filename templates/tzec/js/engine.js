// JavaScript Document
(function($){
	$(function(){
		
		$('a.cbox.iframe').each(function(){
			var href = $(this).attr('href'); var sign = '?';
			if(href.indexOf("?",0) >0){sign = '&'};
			$(this).attr('href',href+sign+'tmpl=component' );
		});
		$('a.cbox.iframe').colorbox({iframe:true,
		  width:function(){
			  var dw = $(this).attr('class').split('boxw_')[1].split(' ')[0];
			  var ww = $(window).width();
			  if(ww<=dw){
				  return '90%';
			  }else{
				return dw;
			  };
			},
		  height:function(){ 
		  		var dh = $(this).attr('class').split('boxh_')[1].split(' ')[0];
				return dh;	
		  }
		 });	
		$('a.lightbox').colorbox({rel:'lightbox'});

		$('a.cbox.inline').colorbox({ inline:true });

		
		$(".slidetoogle").click(function(){
        	$(".menu-top").toggleClass("open");
       	})		
		
		
		ffix();
		$(window).load(function(){
			ffix();
		});


		/*$(function(){    
        $(".rr")
          .click(function(e) {                
              $('.sea').slideToggle("slow");
              $('.call').css("display", "none");                      
           e.preventDefault();
          });           
      });*/

$(document).ready(function() {
	$(".rr").toggle(function(){
		$(".sea").fadeIn();
		$('.sea').css("display", "inline-block");
		$('.call').css("display", "none");
	},
	function(){		
		$(".sea").fadeOut();
		$('.sea').css("display", "inline-block");
		$('.call').css("display", "inline-block");		
	});

        var $tableHeader = [];

        if($('#component table').length){
            $('#component table tr:first > *').each(function(event){
                var $th = $(this).text().replace(/\n/g, '');
                $tableHeader.push($th);
            });

            $('#component table tr:not(:first) > *').each(function(event){
                $(this).attr('data-label', $tableHeader[$(this).index()]);
            });
        }


});
	

	$(function($){
   		$("#phone").mask("+380(99) 999-99-99");
   	 });

        var $windowsWidth = $(window).width();
        var $windowsHeight = $(window).height();


        $('body').on('click', '.menu-top a', function (event) {
            var $link = $(this),
                $parent = $(this).parent();

            if ($windowsWidth <= 1025) {

                if ($parent.hasClass('parent')) {
                    event.preventDefault();
                    if ($parent.hasClass('open')) {
                        $parent.removeClass('open');
                        $('body').removeAttr('style');
                    } else {
                        $parent.addClass('open');
                        $('body').css({overflow: 'hidden'});
                    }
                }
            }
        });
        	
});	
//	

function ffix(){
	var wh = $(window).height(),
		ph = $('#page').height();
	if(wh>ph){ $('#footer').addClass('fix'); }else{ $('#footer').removeClass('fix');}
};



})(jQuery);



    




		
		 
			