// Publishing functions
function publish() {
	var lod = '';
	$("#lod-form :checked").each(function() {
		lod = lod + ' ' + $(this).val();		
	})
		
	var params = {'content': $("#content").val(),
	              'reply_of': $("#reply_of").val(),
	              'location': $("#location").val(),
	              'location_uri': $("#location_uri").val(),
	              'twitter': $("#twitter:checked").length,
	              'sindice': $("#sindice:checked").length,
	              'lod': lod,
	             };		
				
	$.get("ajax/pub.php?" + $.param(params)+getCacheBusterParam(), function(data){
		$("#smob-publish").show("normal");
		$("#smob-publish").html(data);
	});
}

// Adapted from http://www.ajaxray.com/blog/2007/11/09/interactive-character-limit-for-textarea-using-jquery/
function charsleft() {
	var limit = 140;
	var text = $('#content').val(); 
	var textlength = text.length;
	
	if(textlength > limit) {
		$('#charsleft').html(0);
		$('#content').val(text.substr(0, limit));
		return false;
	} else {
		$('#charsleft').html(limit - textlength);
		return true;
	}
}

// Tab generation for the interlinking
function addTab(data) {
	var obj = JSON.parse(data);
	$("#lod-form").append("<div id='" + obj.id + "'>" + obj.html + "</div>");
	$("#tabs").tabs("add", '#'+obj.id, obj.term);
	size = $('#tabs').tabs("length");
}

// LOD links suggestion
function interlink() {
	var text = $('#content').val() + '#'; 
	var words = jQuery.trim(text).split(' ');	
	var current_words = words.length - 1;
	
	if(current_words > numwords) {		
		numwords = current_words;
		words.pop();
		current = words.pop();
		first = current.charAt(0);
		if(first == '#') {
			$.get("ajax/interlink.php?type=tag&term="+urlencode(current)+getCacheBusterParam(), function(data){
				addTab(data);
			});
		}
		else if(first == 'L') {
			if(current.length > 1) {
				second = current.charAt(1);
				if(second == ':') {
					$.get("ajax/interlink.php?type=location&term="+urlencode(current)+getCacheBusterParam(), function(data){
						addTab(data);
					});
				}
			}
		} else if(first == '@') {	
			$.get("ajax/interlink.php?type=user&term="+urlencode(current)+getCacheBusterParam(), function(data){
				addTab(data);
			});
		}
	}
}

// Get news ?
function getnews() {
	var np = $('#np').html(); 
	$.get("ajax/news.php?np="+urlencode(np)+getCacheBusterParam(), function(data) {
		if(data) {
			$("#news").show("normal");
			$('#news').html(data);	
		}
	});
}

// Setup functions
function process(){
	showStatus();
	switch(state) {
		// DB creation	
		case 0:
			log("STEP 1: SMOB database setup ...");
			$("#head").hide("normal");
			$("#smob-db-pane").show("normal");	
			setStep("Go !");
			nextStep();
			break;
		case 1:
			$("#skip").hide();
			install_db_settings();
			nextStep();
			break;
		// SMOB settings	
		case 2:
			log("STEP 3: SMOB settings ...");
			$("#skip").show();
			$("#smob-db-pane").hide("normal");
			$("#smob-settings-pane").show("normal");
			setStep("Go !");
			nextStep();
			break;
		case 3:
			$("#skip").hide();
			install_smob_settings();
			nextStep();
			break;
		// User settings
		case 4:
			log("STEP 4: User settings ...");
			$("#skip").show();
			$("#smob-settings-pane").hide("normal");
			$("#smob-user-pane").show("normal");
			setStep("Go !");
			nextStep();
			break;
		case 5:
			$("#skip").hide();
			install_user_settings();
			nextStep();
			break;
		// End						
		case 6:
			log("STEP 4: Done !");
			$("#create-db-pane").hide("normal");
			$("#smob-settings-pane").hide("normal");
			$("#smob-user-pane").hide("normal");
			$("#step").hide();
			$("#skip").hide();
			$("#done-pane").show("normal");
			break;
		// Default
		default:
	  		log("I'm in trouble - please restart ...");
	}	
}

function resetInstall(){
	log("Ready.");
	$("#smob-db-pane").hide();
	$("#done-pane").hide();
	$("#skip").hide();
	setStep("START!");
	state = 0;
}

function install_db_settings(){
	var host = $("#db-host").val();
	var name = $("#db-name").val();
	var user = $("#db-user").val();
	var pwd = $("#db-pwd").val();
	var store = $("#db-store").val();

	$("#smob-db-pane-in").hide("normal");
	$("#smob-db-pane-out").show("normal");

	$.get("ajax/install.php?cmd=create-db&host="+urlencode(host)+"&name="+name+"&user="+user+"&pwd="+pwd+"&store="+store+getCacheBusterParam(), function(data){
		$("#smob-db-pane-out").html(data);
	});
}

function install_smob_settings(){
	var smob_root = $("#smob-root").val();	
	var purge = $("#smob-purge").val();			
		
	$("#smob-settings-pane-in").hide("normal");
	$("#smob-settings-pane-out").show("normal");
	
	$.get("ajax/install.php?cmd=setup-smob&smob_root="+urlencode(smob_root)+"&purge="+urlencode(purge)+getCacheBusterParam(), function(data){
		$("#smob-settings-pane-out").html(data);
	});		
}

function install_user_settings(){

	var foaf_uri = $("#smob-uri").val();

	var username = $("#smob-username").val();
	var depiction = $("#smob-depiction").val();
	
	var twitter_read = $('input[name=smob-twitter-read]:checked').val();
	var twitter_post = $('input[name=smob-twitter-post]:checked').val();

	var twitter_login = $("#smob-twitter-login").val();
	var twitter_pass = $("#smob-twitter-pass").val();

	var auth = $('input[name=smob-auth]:checked').val()	

	$("#smob-user-pane-in").hide("normal");
	$("#smob-user-pane-out").show("normal");

	$.get("ajax/install.php?cmd=setup-user&foaf_uri="+urlencode(foaf_uri)+"&username="+urlencode(username)+"&depiction="+urlencode(depiction)+"&twitter_login="+urlencode(twitter_login)+"&twitter_pass="+urlencode(twitter_pass)+"&twitter_read="+urlencode(twitter_read)+"&twitter_post="+urlencode(twitter_post)+"&auth="+auth+getCacheBusterParam(), function(data){
		$("#smob-user-pane-out").html(data);
	});
}

/*
function install_user_settings(){
	var smob_root = $("#smob-root").val();	
	var purge = $("#smob-purge").val();			
	var client_uri = $("#smob-uri").val();
	var client_twitter_login = $("#smob-twitter-login").val();
	var client_twitter_pass = $("#smob-twitter-pass").val();
	var auth = $('input[name=smob-auth]:checked').val()	
		
	$("#smob-config-pane-in").hide("normal");
	$("#smob-config-pane-out").show("normal");
	
	$.get("ajax/install.php?cmd=setup-smob&smob_root="+urlencode(smob_root)+"&purge="+urlencode(purge)+"&client_uri="+urlencode(client_uri)+"&client_twitter_login="+urlencode(client_twitter_login)+"&client_twitter_pass="+urlencode(client_twitter_pass)+"&auth="+auth+getCacheBusterParam(), function(data){
		$("#smob-config-pane-out").html(data);
	});		
}*/

function log(msg){
	$("#console").text(msg);	
}

function setStep(msg){
	$("#step").text(msg);	
}

function showStatus(){
	$("#status").text(state + " of 6");	
}

function nextStep(){
	if(state <= maxstate) state++;
	else state = maxstate;
}

// Helper functions
function urlencode(str) {
    // URL-encodes string  
    // 
    // version: 907.503
    // discuss at: http://phpjs.org/functions/urlencode
    // +   original by: Philip Peterson
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: AJ
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: travc
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Lars Fischer
    // +      input by: Ratheous
    // %          note 1: info on what encoding functions to use from: http://xkr.us/articles/javascript/encode-compare/
    // *     example 1: urlencode('Kevin van Zonneveld!');
    // *     returns 1: 'Kevin+van+Zonneveld%21'
    // *     example 2: urlencode('http://kevin.vanzonneveld.net/');
    // *     returns 2: 'http%3A%2F%2Fkevin.vanzonneveld.net%2F'
    // *     example 3: urlencode('http://www.google.nl/search?q=php.js&ie=utf-8&oe=utf-8&aq=t&rls=com.ubuntu:en-US:unofficial&client=firefox-a');
    // *     returns 3: 'http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3Dphp.js%26ie%3Dutf-8%26oe%3Dutf-8%26aq%3Dt%26rls%3Dcom.ubuntu%3Aen-US%3Aunofficial%26client%3Dfirefox-a'
                             
    var hash_map = {}, unicodeStr='', hexEscStr='';
    var ret = (str+'').toString();
    
    var replacer = function(search, replace, str) {
        var tmp_arr = [];
        tmp_arr = str.split(search);
        return tmp_arr.join(replace);
    };
    
    // The hash_map is identical to the one in urldecode.
    hash_map["'"]   = '%27';
    hash_map['(']   = '%28';
    hash_map[')']   = '%29';
    hash_map['*']   = '%2A';
    hash_map['~']   = '%7E';
    hash_map['!']   = '%21';
    hash_map['%20'] = '+';
    hash_map['\u00DC'] = '%DC';
    hash_map['\u00FC'] = '%FC';
    hash_map['\u00C4'] = '%D4';
    hash_map['\u00E4'] = '%E4';
    hash_map['\u00D6'] = '%D6';
    hash_map['\u00F6'] = '%F6';
    hash_map['\u00DF'] = '%DF';
    hash_map['\u20AC'] = '%80';
    hash_map['\u0081'] = '%81';
    hash_map['\u201A'] = '%82';
    hash_map['\u0192'] = '%83';
    hash_map['\u201E'] = '%84';
    hash_map['\u2026'] = '%85';
    hash_map['\u2020'] = '%86';
    hash_map['\u2021'] = '%87';
    hash_map['\u02C6'] = '%88';
    hash_map['\u2030'] = '%89';
    hash_map['\u0160'] = '%8A';
    hash_map['\u2039'] = '%8B';
    hash_map['\u0152'] = '%8C';
    hash_map['\u008D'] = '%8D';
    hash_map['\u017D'] = '%8E';
    hash_map['\u008F'] = '%8F';
    hash_map['\u0090'] = '%90';
    hash_map['\u2018'] = '%91';
    hash_map['\u2019'] = '%92';
    hash_map['\u201C'] = '%93';
    hash_map['\u201D'] = '%94';
    hash_map['\u2022'] = '%95';
    hash_map['\u2013'] = '%96';
    hash_map['\u2014'] = '%97';
    hash_map['\u02DC'] = '%98';
    hash_map['\u2122'] = '%99';
    hash_map['\u0161'] = '%9A';
    hash_map['\u203A'] = '%9B';
    hash_map['\u0153'] = '%9C';
    hash_map['\u009D'] = '%9D';
    hash_map['\u017E'] = '%9E';
    hash_map['\u0178'] = '%9F';
    
    // Begin with encodeURIComponent, which most resembles PHP's encoding functions
    ret = encodeURIComponent(ret);

    for (unicodeStr in hash_map) {
        hexEscStr = hash_map[unicodeStr];
        ret = replacer(unicodeStr, hexEscStr, ret); // Custom replace. No regexing
    }
    
    // Uppercase for full PHP compatibility
    return ret.replace(/(\%([a-z0-9]{2}))/g, function(full, m1, m2) {
        return "%"+m2.toUpperCase();
    });
}

function getCacheBusterParam(){
	// http://mousewhisperer.co.uk/js_page.html
	return  "&rcb=" + parseInt(Math.random()*99999999); 
}


// Outputs to console and list
function log(message) {
  var state = document.createElement('div');
  state.innerHTML = message;
  document.getElementById('main').appendChild(state);
}

function html_entity_decode(s) {
  var t=document.createElement('textarea');
  t.innerHTML = s;
  var v = t.value;
  t.parentNode.removeChild(t);
  return v;
}
