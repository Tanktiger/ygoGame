var viewport = {
    width  : $(window).width(),
    height : $(window).height()
};
//var SingleCardData = null;
var userLang = navigator.language || navigator.userLanguage; 
$(document).bind('pagecreate', function(){
	function setSingleCard (data, isMulti = false) {
		if (isMulti) {
			$('#SingleCard .backLink').attr('href', '#MultiCards');
		}
		$.each(data, function (key, item) {
			ebayCall(item);
			$('#SingleCard .cardNameEnglish').text(item.name_en);
			$('#SingleCard .cardNameEnglishAlt').text(item.name_en_alternate);
			$('#SingleCard .cardNameGerman').text(item.name_de);
			$('#SingleCard .cardCode').text(item.code);
			$('#SingleCard .cardEffectEnglish').text(item.effect_en);
			$('#SingleCard .cardEffectGerman').text(item.effect_de);
			$('#SingleCard .cardPicture').attr('src', item.pic_url);
			$('#SingleCard .cardPicture').css('max-width', viewport.width);
			$('#SingleCard .cardPicture').css('max-height', viewport.height);
		});
	}
	
	function setMultiCard (data) {
		var list = $('#MultiCardList');
		$.each(data, function (key, item) {
			//data-corners="false" data-shadow="false" data-iconshadow="true" data-wrapperels="div" data-icon="arrow-r" data-iconpos="right"
			var li = '<li data-corners="false" data-shadow="false" data-iconshadow="true" data-wrapperels="a" data-icon="arrow-r" data-iconpos="right"><a href="#SingleCard" class="multiCard" data-cardId="'+item.id+'">' + item.name_de + '</a></li>';
			list.append(li);
		});
		list.listview('refresh');
	}
	
	$('#mainSearch').on('click','.submit', function(e) {
		$.mobile.loading( 'show', {theme: "b", text: "...", textVisible: true});
		//cache the form element for use in this function
	    var $this = $(this).parents('form');
	    //prevent the default submission of the form
	    if (e.preventDefault) e.preventDefault();
	    //run an AJAX post request to your server-side script, $this.serialize() is the data from your form being added to the request
	    $.ajax({
	    	url: $this.attr('action') + '?ask=main',
	    	data: $this.serialize(),
	    	dataType: 'jsonp',
	    	jsonp: 'jsoncallback',
			success: function (data, status) {
				//go to div SingleCard oder MultiCard - je nachdem wie viele im Ergebnis kommen
				//hänge alle übergebenen Werte in das Skelett
				if (Object.keys(data).length == 1) {
					$.mobile.changePage($("#SingleCard"));
					setSingleCard(data);
				} else {
					$.mobile.changePage($("#MultiCards"));
					setMultiCard(data);
				}
				$.mobile.loading( 'hide');
			}
	    });
	});
	
	$(document).on('click','#allCardsLink', function(e) {
		$.mobile.loading( 'show', {theme: "b", text: "...", textVisible: true});
	    var $this = $(this);
	    $.ajax({
	    	url: '/server/ask.php/?ask=all',
	    	dataType: 'jsonp',
	    	jsonp: 'jsoncallback',
			success: function (data, status) {
				setMultiCard(data);
				$.mobile.loading( 'hide');
			}
	    });
	});
	$(document).on('click','#MultiCardList .multiCard', function(e) {
		$.mobile.loading( 'show', {theme: "b", text: "...", textVisible: true});
	    var $this = $(this);
	    $.ajax({
	    	url: '/server/ask.php/?ask=singleId&id='+ $this.attr('data-cardId'),
	    	dataType: 'jsonp',
	    	jsonp: 'jsoncallback',
			success: function (data, status) {
//				$.mobile.changePage($("#SingleCard"));
				setSingleCard(data, true);
			}
	    });
	    $.mobile.loading( 'hide');
	});
	
	function ebayCall (data) {
		var name = data.name_en + '%20' + data.name_en_alternate;
		var lang = 'US';
		if (userLang == 'de-DE') {
			lang = 'DE';
			name = data.name_de;
		}
		var url = "http://svcs.ebay.com/services/search/FindingService/v1";
	    url += "?OPERATION-NAME=findItemsByKeywords";
	    url += "&SERVICE-VERSION=1.0.0";
	    url += "&SECURITY-APPNAME=NoCompan-44f9-44d0-99f3-54a58c06d859";
	    url += "&GLOBAL-ID=EBAY-" + lang;
	    url += "&RESPONSE-DATA-FORMAT=JSON";
	    url += "&callback=_cb_findItemsByKeywords";
	    url += "&REST-PAYLOAD";
	    url += "&keywords=YuGiOh%20" + name;
	    url += "&paginationInput.entriesPerPage=5";
	    url += "&itemFilter(0).name=GetItFastOnly&itemFilter(0).value=true";
	    
	    //alles was bei succes geschen soll muss in die funktion _cb_findItemsByKeywords
	    $.ajax({
            url: url,
            dataType: "script"
        });
	}
});
function _cb_findItemsByKeywords(root) {
	var items = root.findItemsByKeywordsResponse[0].searchResult[0].item || [];
	var offerList = $('.ebayItemList');
	var currency = '$';
	if (userLang == 'de-DE') {
		currency = '€';
	}
	$.each(items, function(key, item) {
		console.log(item);
		var li = '<li><a href="'+item.viewItemURL+'">' + item.title + '</a>&nbsp;<span>'+item.sellingStatus[0].currentPrice[0].__value__+'&nbsp;'+currency+'</span></li>';
		offerList.append(li);
	});
}  // End _cb_findItemsByKeywords() function