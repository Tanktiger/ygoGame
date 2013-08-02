var viewport = {
    width  : $(window).width(),
    height : $(window).height()
};

$(document).ready(function(){
	$('#mainSearch').on('click','.submit', function(e) {
		//cache the form element for use in this function
	    var $this = $(this).parents('form');
	    //prevent the default submission of the form
	    if (e.preventDefault) e.preventDefault();
	    //run an AJAX post request to your server-side script, $this.serialize() is the data from your form being added to the request
	    $.ajax({
	    	url: $this.attr('action'),
	    	data: $this.serialize(),
	    	dataType: 'jsonp',
	    	jsonp: 'jsoncallback',
			success: function (data, status) {
				//go to div SingleCard
				//hänge alle übergebenen Werte in das Skelett
				$.mobile.changePage($("#SingleCard"));
				$('.cardName').text(data.name);
				$('.cardPicture').attr('src', data.url);
				$('.cardPicture').css('max-width', viewport.width);
				$('.cardPicture').css('max-height', viewport.height);
				$('.cardCode').text(data.language);
				//bei mehreren Ergebnissen zeige overlay mit ergebnissen
			}
	    });
	});
});