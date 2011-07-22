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
  
  <script type="text/javascript" src="http://localhost/smob/js/jquery-dynamic-form.js"></script>  
    <script type="text/javascript" src="http://localhost/smob/js/jquery.form.js"></script> 
 
 
  <base href="" /> 
  <script type="text/javascript"> 
    $(document).ready(function(){
      $("#tabs_interest").tabs();
      $('#interest').focus(function() {
        $('.interest-details').show();
      });
      numwords_interest = 0;
      $('#interest').keyup(function(){
        interlink_interest('#interest', '#lod_interest', '#tabs_interest');
      });
    });
  </script>
  <script type="text/javascript"> 
    
    $(document).ready(function() {
      $.getJSON('/smob/relationship.json', function ( data ) { 
        console.debug( data ); 
        for (rel in data) {
          if (rel.indexOf("http://purl.org/vocab/relationship/") === 0) {
            if (data[rel].hasOwnProperty("http://www.w3.org/2000/01/rdf-schema#label")) {
              var label = data[rel]['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
              var option_data =  "<option value='"+rel+"' >"+label+ "</option>";
              $('#rel_type0').append(option_data);           
            }
          }
        }
      });

      $('#add_rel').click(function(e) {
        e.preventDefault();
        addRel();
      });

      $('#del_rel').click(function(e) {
        e.preventDefault();
        $('#rel_fieldset').remove();
      });

      $('#private_submit').click(function(e) {
        e.preventDefault();
        var user_uri = document.location.href;
        user_uri.replace("/private","");
        post_private_profile(user_uri);
      });
      
      
      
    });
  </script>
</head> 
 
<body about="http://localhost/smob/" typeof="smob:Hub sioct:Microblog"> 
 
<div id="full"> 
 
<div id="header"> 
<h1><a href="http://localhost/smob/">SMOB</a></h1> 
<h2><span class="smob">S</span>emantic-<span class="smob">M</span>icr<span class="smob">OB</span>logging</h2> 
</div> 
 
<div id="main"> 
 
<div class="left">  
  
<h2>Private profile</h2>
    <form id="private_form">
      </br><b>Interests</b>
      
      <fieldset id="interest_fieldset"><legend>Interest</legend> 
      <textarea id="interest" name="interest" rows="1" cols="30"></textarea> 
      <div class="interest-details" style="display: none;"> 
      <div id="lod_interest">Links will be suggested while typing ... (space required after each #tag)
        <div id="tabs_interest"><ul></ul></div> 
      </div> 
      </fieldset> 
      
      </br><b>relationships</b>
      
      <div id="rel_block">
      
      <fieldset id="rel_fieldset0"><legend>Relationship</legend> 
        <select id="rel_type0" name="rel_type0"> 
        <option value=""></option> 
        </select> 
        <input id="person0" name="person0" type="text" cols='10' />
      </fieldset> 
      
      </div> 

      <p><a id="add_rel" href="">[+]</a></p>
       
      <input type="hidden" id="counter" value="1">
       
      <button id="private_submit" class="content-details">Save</button>
    </form> 
 
<h2>Result</h2>
    <div id="result"></div>
    <div id="privacy_result"></div>
 
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
<li><a href='http://localhost/smob/pp'>Private Profile</a></li> 
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
 
</body> 
 
</html> 
 
