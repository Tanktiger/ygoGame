
var viewport = {
    width  : $(window).width(),
    height : $(window).height()
};
//var SingleCardData = null;
var userLang = navigator.language || navigator.userLanguage; 
$( document ).on( "mobileinit", function() {
    // Make your jQuery Mobile framework configuration changes here!
	$.support.cors = true;
    $.mobile.allowCrossDomainPages = true;
    $.mobile.page.prototype.options.domCache = false;
});
$(document).bind('pagecreate', function(){
	
	$('#mainSearch').unbind('click').on('click','.submit', function(e) {
		$.mobile.loading( 'show', {theme: "b", text: "...", textVisible: true});
		//cache the form element for use in this function
	    var $this = $(this).parents('form');
	    //prevent the default submission of the form
	    if (e.preventDefault) e.preventDefault();
	    //run an AJAX post request to your server-side script, $this.serialize() is the data from your form being added to the request
	    $.ajax({
	    	type: 'GET',
	    	url: 'http://tanktiger.square7.ch/server/ask.php?ask=main',//$this.attr('action') + '?ask=main',
	    	data: $this.serialize(),
	    	cache: false,
	    	dataType: 'jsonp',
	    	crossDomain: true,
			success: function (data, status) {
				//go to div SingleCard oder MultiCard - je nachdem wie viele im Ergebnis kommen
				//hänge alle übergebenen Werte in das Skelett
				var list = $("#SingleCard");
				if (Object.keys(data).length == 1) {
					setSingleCard(data, false);
					$.mobile.changePage(list,{
						  allowSamePageTransition : true,
						  transition : 'none',
						  showLoadMsg : true,
						  reloadPage : true
						 });
					list.listview('refresh');
				} else if(Object.keys(data).length > 1) {
					$.mobile.changePage( $('#MultiCards') ,{
						  allowSamePageTransition : true,
						  transition : 'none',
						  showLoadMsg : true,
						  reloadPage : true
						 });
					setMultiCard(data);
				} else {
					$.mobile.loading( 'hide');
					$( "#noResult" ).show();
					$( "#noResult" ).popup();
					$( "#noResult" ).popup( "open" );
				}
				$.mobile.loading( 'hide');
				$( "#noResult" ).hide();
			}
	    });
	});
	
	$(document).unbind('click').on('click','#allCardsLink', function(e) {
		$.mobile.loading( 'show', {theme: "b", text: "...", textVisible: true});
	    var $this = $(this);
	    $.ajax({
	    	url: 'http://tanktiger.square7.ch/server/ask.php?ask=all',
	    	dataType: 'jsonp',
	    	cache: false,
	    	jsonp: 'jsoncallback',
			success: function (data, status) {
				setMultiCard(data);
			}
	    });
	    $.mobile.loading( 'hide');
	});
	$(document).unbind('click').on('click','#MultiCardList .multiCard', function(e) {
		$.mobile.loading( 'show', {theme: "b", text: "...", textVisible: true});
	    var $this = $(this);
	    $.ajax({
	    	url: 'http://tanktiger.square7.ch/server/ask.php?ask=singleId&id='+ $this.attr('data-cardId'),
	    	dataType: 'jsonp',
	    	cache: false,
			success: function (data, status) {
				setSingleCard(data, true);
			}
	    });
	    $.mobile.loading( 'hide');
	});
	
});
function ebayCall (data) {
	console.log('ebayCall');
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
    url += "&keywords=YuGiOh%20" + encodeURI(name);
    url += "&paginationInput.entriesPerPage=5";
    url += "&itemFilter(0).name=GetItFastOnly&itemFilter(0).value=true";
    console.log(url);
    //alles was bei succes geschen soll muss in die funktion _cb_findItemsByKeywords
    $.ajax({
        url: url,
        dataType: "script"
    });
    console.log('ajaxCalled');
}
function _cb_findItemsByKeywords(root) {
	var items = root.findItemsByKeywordsResponse[0].searchResult[0].item || [];
	var offerList = $('.ebayItemList');
	var currency = '$';
	if (userLang == 'de-DE') {
		currency = '€';
	}
	$.each(items, function(key, item) {
		var li = '<li data-corners="false" data-shadow="false" data-iconshadow="true" data-wrapperels="a" data-icon="arrow-r" data-iconpos="right"><a href="'+item.viewItemURL+'">' + item.title + '<span>&nbsp;'+item.sellingStatus[0].currentPrice[0].__value__+'&nbsp;'+currency+'</span></a></li>';
		offerList.append(li);
	});
}  // End _cb_findItemsByKeywords() function

function setSingleCard (data, isMulti) {
	if (isMulti) {
		$('#SingleCard .backLink').attr('data-ajax', 'true');
		$('#SingleCard .backLink').attr('href', '#MultiCards');
	} else {
		$('#SingleCard .backLink').attr('data-ajax', 'false');
		$('#SingleCard .backLink').attr('href', '#home');
		
	}
	$.each(data, function (key, item) {
		ebayCall(item);
		
		var cardNameEnglish = $('#SingleCard .cardNameEnglish');
		if (item.name_en){
			cardNameEnglish.parents('.cardItem').show();
			cardNameEnglish.text(item.name_en);
		} else {
			cardNameEnglish.parents('.cardItem').hide();
		}
		
		var cardNameEnglishAlt = $('#SingleCard .cardNameEnglishAlt');
		if (item.name_en_alternate){
			cardNameEnglishAlt.text(item.name_en_alternate);
			cardNameEnglishAlt.parents('.cardItem').show();
		} else {
			cardNameEnglishAlt.parents('.cardItem').hide();
		}
		
		var cardNameGerman = $('#SingleCard .cardNameGerman');
		if (item.name_de){
			cardNameGerman.text(item.name_de);
			cardNameGerman.parents('.cardItem').show();
		} else {
			cardNameGerman.parents('.cardItem').hide();
		}
		
		var cardCode = $('#SingleCard .cardCode');
		if (item.code){
			cardCode.text(item.code);
			cardCode.parents('.cardItem').show();
		} else {
			cardCode.parents('.cardItem').hide();
		}
		
		var cardEffectEn = $('#SingleCard .cardEffectEnglish');
		if (item.effect_en){
			cardEffectEn.text(item.effect_en);
			cardEffectEn.parents('.cardItem').show();
		} else {
			cardEffectEn.parents('.cardItem').hide();
		}
		
		var cardEffectGer = $('#SingleCard .cardEffectGerman');
		if (item.effect_de){
			cardEffectGer.text(item.effect_de);
			cardEffectGer.parents('.cardItem').show();
		} else {
			cardEffectGer.parents('.cardItem').hide();
		}
		
		var cardPicture = $('#SingleCard .cardPicture');
		if (item.pic_url){
			cardPicture.attr('src', item.pic_url);
			cardPicture.css('max-width', viewport.width);
			cardPicture.css('max-height', viewport.height);
			cardPicture.show();
			cardPicture.lazyload();
			cardPicture.attr('src', cardPicture.attr('src')+'?'+Math.random());
		} else {
			cardPicture.hide();
		}
		
		var cardLevel = $('#SingleCard .cardLevel');
		if (item.level){
			var levelImg = '<img src="/img/level.svg" width="20" height="20" border="0" alt="Level">';
			var levelHtml = item.level + '&nbsp;';
			for (var i = 1; i <= item.level; i++) {
				levelHtml += levelImg;
			}
			cardLevel.html(levelHtml);
			cardLevel.parents('.cardItem').show();
			lazyLoadPictures(cardLevel);
		} else {
			cardLevel.parents('.cardItem').hide();
		}
		
		var cardRank = $('#SingleCard .cardRank');
		if (item.rank){
			var rankImg = '<img src="/img/rank.svg" width="20" height="20" border="0" alt="Rank">';
			var rankHtml = item.rank + '&nbsp;';
			for (var i = 1; i <= item.rank; i++) {
				rankHtml += rankImg;
			}
			cardRank.html(rankHtml);
			cardRank.parents('.cardItem').show();
			lazyLoadPictures(cardRank);
		} else {
			cardRank.parents('.cardItem').hide();
		}
		
		var cardValues = $('#SingleCard .cardValues');
		if (item.atk && item.def){
			cardValues.text(item.atk + '/' + item.def);
			cardValues.parents('.cardItem').show();
		} else {
			cardValues.parents('.cardItem').hide();
		}
		
		var cardAttribute = $('#SingleCard .cardAttribute');
		if (item.attribute){
			var attributeImg = '<img src="/img/' + item.attribute.replace(/ /, '') + '.png" width="30" height="30" border="0" alt="Attribute">';
			var attributeHtml = item.attribute + '&nbsp;' + attributeImg;
			cardAttribute.html(attributeHtml);
			cardAttribute.parents('.cardItem').show();
			lazyLoadPictures(cardAttribute);
		} else {
			cardAttribute.parents('.cardItem').hide();
		}
		
		var cardProperties = $('#SingleCard .cardProperties');
		if (item.propertys){
			var propertysImg = '<span></span>';
			if (item.propertys.search(/Normal/) == -1) {
				propertysImg = '<img src="/img/' + item.propertys.replace(/ /, '') + '.png" width="30" height="30" border="0" alt="Property">';
			}
			var propertysHtml = item.propertys + '&nbsp;' + propertysImg;
			cardProperties.html(propertysHtml);
			cardProperties.parents('.cardItem').show();
			lazyLoadPictures(cardProperties);
		} else {
			cardProperties.parents('.cardItem').hide();
		}
		
		var cardType = $('#SingleCard .cardType');
		if (item.type){
			var cardTypeHtml = item.type;
			var cardTypes = null;
			if (item.type.search(/Spell Card/) != -1) {
				cardTypes = 'SPELL';
			} else if (item.type.search(/Trap Card/) != -1) {
				cardTypes = 'TRAP';
			}
			var img = '<span></span>';
			if (cardTypes !== null) {
				img = '<img src="/img/' + cardTypes.replace(/ /, '') + '.png" width="30" height="30" border="0" alt="Attribute">';
				cardTypeHtml += img;
			}
			cardType.html(cardTypeHtml);
			cardType.parents('.cardItem').show();
			lazyLoadPictures(cardType);
		} else {
			cardType.parents('.cardItem').hide();
		}
		
		var cardFusionMaterial = $('#SingleCard .cardFusionMaterial');
		if (item.fusion_material){
			cardFusionMaterial.text(item.fusion_material);
			cardFusionMaterial.parents('.cardItem').show();
		} else {
			cardFusionMaterial.parents('.cardItem').hide();
		}
		
		var cardMaterial = $('#SingleCard .cardMaterial');
		if (item.material){
			cardMaterial.text(item.material);
			cardMaterial.parents('.cardItem').show();
		} else {
			cardMaterial.parents('.cardItem').hide();
		}
		
	});
	
}

function setMultiCard (data) {
	var list = $('#MultiCardList');
	list.empty();
	$.each(data, function (key, item) {
		var name = item.name_en || item.name_en_alternate;
		if (userLang == 'de-DE') {
			name = item.name_de;
		}
		var li = '<li data-corners="false" data-shadow="false" data-iconshadow="true" data-wrapperels="a" data-icon="arrow-r" data-iconpos="right">';
		var a =	'<a href="#SingleCard" class="multiCard" data-cardId="'+item.id+'">' + name;
		
		li += a;
		li += '</a></li>';
		list.append(li);
	});
	list.listview('refresh');
}

function lazyLoadPictures(dom) {
	var imgs = dom.find('img');
	$.each(imgs, function(key, item) {
		$(item).lazyload();
		$(item).attr('src', $(item).attr('src')+'?'+Math.random());
	});
}