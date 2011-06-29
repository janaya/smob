<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" 
 	"http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"> 
<html
	xmlns="http://www.w3.org/1999/xhtml" 
	xmlns:v="urn:schemas-microsoft-com:vml"
	xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:dcterms="http://purl.org/dc/terms/"
	xmlns:foaf="http://xmlns.com/foaf/0.1/" 
	xmlns:sioc="http://rdfs.org/sioc/ns#"
	xmlns:sioct="http://rdfs.org/sioc/types#"
	xmlns:ctag="http://commontag.org/ns#"
	xmlns:opo="http://online-presence.net/opo/ns#"
	xmlns:smob="http://smob.me/ns#"
	xmlns:moat="http://moat-project.org/ns#"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:rev="http://purl.org/stuff/rev#"
xml:lang="fr"> 
 
<head profile="http://ns.inria.fr/grddl/rdfa/"> 
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" /> 
  <title>SMOB</title> 
  <link rel="icon" type="image/png" href="http://localhost/smob/img/smob-icon.png" /> 
  <link rel="stylesheet" type="text/css" href="http://localhost/smob/css/style.css" /> 
 
  <link type="text/css" href="http://jqueryui.com/latest/themes/base/jquery.ui.all.css" rel="stylesheet" /> 
 
  <script type="text/javascript" src="http://www.google.com/jsapi"></script>	
  <script type="text/javascript">	
    google.load("jquery", "1.4.1");
    google.load("jqueryui", "1.7.2");
  </script> 
  <script type="text/javascript" src="http://localhost/smob/js/jquery.timers-1.2.js"></script> 
  <script type="text/javascript" src="http://localhost/smob/js/jquery.autocomplete-min.js"></script> 
  <script type="text/javascript" src="http://localhost/smob/js/jquery.rating.js"></script> 
 
  <script type="text/javascript" src="http://localhost/smob/js/smob.js"></script> 
 
  <base href="" /> 
  <script type="text/javascript"> 
	$(document).ready(function(){
		$("#tabs_hashtag").tabs();
		$("#tabs_interest").tabs();
	});
  </script> 
  		<script type="text/javascript"> 
		$(document).ready(function() {
		  $('#content_hashtag').focus(function() {
			  $('.content_hashtag-details').show();
		  });
		  $('#content_interest').focus(function() {
			  $('.content_interest-details').show();
		  });
			numwords = 0;
			numwords_interest = 0;
			$('#content_hashtag').keyup(function(){
				interlink('#content_hashtag', '#lod-form_hashtag', '#tags_hashtag');
			});
			$('#content_interest').keyup(function(){
				interlink_interest('#content_interest', '#lod-form_interest', '#tags_interest');
			});
		});
		</script>
    <script type="text/javascript"> 
    function generate_pp() {
        
      var params = {'hashtag': $("#hashtag-form").val(),
                    'interest': $("#interest-form").val(),
                    'rel': $("#relationships").val(),
                   };    
            
      var pp = "";
      //$.get("ajax/pub.php?" + $.param(params)+getCacheBusterParam(), function(data){
      //  $("#generating_pp").show("normal");
      //  $("#generating_pp").html(data);
      //});
      
      $("#generating_pp").show("normal");
      //$("#generating_pp").html(data);
      
      $("#generated_pp").show("normal");
      //$("#generated_pp").html(pp);
      $("#generating_pp").html("<br/><em>Privacy Preference Generated</em>");
    }
 
   
    $(document).ready(function() {
      $.getJSON('/smob/relationship.json', function ( data ) { 
        console.debug( data ); 
        for (rel in data) {
          if (rel.indexOf("http://purl.org/vocab/relationship/") === 0) {
          //if (rel.substring(0, "http://purl.org/vocab/relationship/".lenght) === "http://purl.org/vocab/relationship/") {
            //console.log(data[rel]['http://www.w3.org/2000/01/rdf-schema#label']);
            if (data[rel].hasOwnProperty("http://www.w3.org/2000/01/rdf-schema#label")) {
              var label = data[rel]['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
              //var desc = data[rel]['http://www.w3.org/2004/02/skos/core#definition'][0]['value'];
              //var option_data =  "<option value='"+rel+"' label='"+desc+"'>"+label+ "</option>";
              var option_data =  "<option value='"+rel+"' >"+label+ "</option>";
              $('#relationships').append(option_data);           
            }
          }
        }
      });
      $("#generate_pp").click(function () {
        console.log("generating...");
        generate_pp();
      });
    });
  </script></head> 
 
<body about="http://localhost/smob/" typeof="smob:Hub sioct:Microblog"> 
 
<div id="full"> 
 
<div id="header"> 
<h1><a href="http://localhost/smob/">SMOB</a></h1> 
<h2><span class="smob">S</span>emantic-<span class="smob">M</span>icr<span class="smob">OB</span>logging</h2> 
</div> 
 
<div id="main"> 
 
<div class="left">	
	
<h2>Privacy settings</h2>
    <form id="pp-form"> 
      </br><b>Resource condition</b>
      <fieldset><legend>Hashtag that the microposts must contain</legend> 
      <textarea name="content_hashtag" id="content_hashtag" rows="1" cols="30"></textarea> 
			<div class="content_hashtag-details" style="display: none;"> 
      <div id="lod-form_hashtag">Links will be suggested while typing ... (space required after each #tag)
				<div id="tabs_hashtag"><ul></ul></div> 
			</div> 
      </fieldset> 
      </br><b>Access Space</b>
      <fieldset><legend>Interest that the subscribers must have to receive the micropost</legend> 
      <textarea name="content_interest" id="content_interest" rows="1" cols="30"></textarea> 
			<div class="content_interest-details" style="display: none;"> 
      <div id="lod-form_interest">Links will be suggested while typing ... (space required after each #tag)
				<div id="tabs_interest"><ul></ul></div> 
			</div> 
      </fieldset> 
      </br>
      OR<input type="radio"  name="interest_rel" value="0" checked="checked"/> AND <input type="radio" name="interest_rel" value="1" /> 
      </br>
      <fieldset><legend>Relationship that the subscriber must have with me</legend> 
      <div id="rel-form"> 
        <select id="relationships"> 
        <option value='' ></option> 
        </select> 
      </div> 
      </fieldset> 
    </form> 
 
      <button id="generate_pp" class="content_hashtag-details">Generate Privacy Preferences</button> 
    
			<div id="generating_pp" style="display: none;"> 
				<br/><em>Generating Privacy Preference...</em> 
			</div> 
			<div id="generated_pp" style="display: none;"> 
			  <br/>
			  
<h3>Privacy preference</h3>
        <div class="post external">
        <pre>
  &lt;http://localhost/smob/pp/1&gt; a ppo:PrivacyPreference;
    ppo:appliesToResource 
     &lt;http://rdfs.org/sioc/ns#MicroblogPost&gt;;
    ppo:hasCondition [
      ppo:hasProperty tag:Tag;
      ppo:resourceAsObject 
       &lt;http://dbpedia.org/resource/Semantic&gt;
    ];
   ppo:assignAccess acl:Read;
   ppo:hasAccessSpace [
     ppo:hasAccessQuery 'SELECT ?user WHERE { 
  ?user foaf:topic_interest &lt;http://dbpedia.org/resource/Semantic_Web&gt;' .}
  ] .
        </pre>
        </div>
			</div>	
 
</div> 
 
<div class="right"> 
 
<h2>Navigation</h2> 
<ul> 
<li><a href='http://localhost/smob/'>Home</a></li> 
<li><a href='http://localhost/smob/map'>Map view</a></li> 
<li><a href='http://localhost/smob/sparql'>SPARQL</a></li> 
</ul> 
 
<h2>People</h2> 
<ul> 
<li><a href='http://localhost/smob/me'>Owner</a> [<a href='http://localhost/smob/me/rss'>RSS</a>]</li> 
<li><a href='http://localhost/smob/followings'>Followings</a></li> 
<li><a href='http://localhost/smob/followers'>Followers</a></li> 
<li><a href='http://localhost/smob/replies'>@replies</a></li> 
</ul> 
 
<h2>Hub owner</h2> 
<ul> 
<li><a href='http://localhost/smob/auth'>Authenticate</a></li> 
<li><a href='http://localhost/smob/pp'>Privacy Settings</a></li> 
</ul> 
	
</div> 
 
<div style="clear: both;"> </div> 
</div> 
 
<div id="footer"> 
Powered by <a href="http://smob.me/">SMOB</a> 2.2 thanks to <a href="http://www.w3.org/2001/sw/">Semantic Web</a> and <a href="http://linkeddata.org">Linked Data</a> technologies.<br/> 
This page is valid <a href="http://validator.w3.org/check?uri=referer">XHTML</a> and <a href="http://www.w3.org/2007/08/pyRdfa/extract?uri=referer">contains RDFa markup</a>.
<br/> 
</div> 
 
</div> 
 
<script type='text/javascript'> 
var options, a;
jQuery(function(){
	options = { 
		serviceUrl:'http://localhost/smob/ajax/geonames.php', 
		minChars:2, 
		onSelect: function(value, data) { 
			$('#location_uri').val(data);
		}, 
	};
	a = $('#location').autocomplete(options);
});
</script> 
 
</body> 
 
</html> 
 
