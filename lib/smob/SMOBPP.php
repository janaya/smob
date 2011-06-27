<?php

class SMOBPP {
    
  public function ppform() {
    $form_pp = <<<__END__
    <form id="pp-form"> 
      Condition
      <fieldset><legend>Hashtag that the microposts must contain</legend> 
      <div id="hashtag-form">rdf 
      </div> 
      </fieldset>
      Access Space 
      <fieldset><legend>Interest that the subscribers must have to receive the micropost</legend> 
      <div id="interest-form">semantic web
      </div> 
      </fieldset> 
 
      OR<input type="radio"  name="interest_rel" value="0" checked="checked"/> AND <input type="radio" name="interest_rel" value="1" />
      
      <fieldset><legend>Relationship that the subscriber must have with me</legend> 
      <div id="rel-form">
        <select id="relationships">
        <option value='' ></option>
        </select>
      </div> 
      </fieldset> 
 
      <button id="generate-pp" class="content-details">Generate Privacy Preferences</button> 
    </form> 
    
			<div id="generating-pp" style="display: none;">
				<br/><em>Generating Privacy Preferences ...</em>
			</div>
__END__;
    return $form_pp;
  }
  
  public function ppjs() {
    $pp_js = <<<__END__
  <script type="text/javascript">
    function generate_pp() {
        
      var params = {'hashtag': $("#hashtag-form").val(),
                    'interest': $("#interest-form").val(),
                    'rel': $("#relationships").val(),
                   };    
            
      $.get("ajax/pub.php?" + $.param(params)+getCacheBusterParam(), function(data){
        $("#generating-pp").show("normal");
        $("#generating-pp").html(data);
      });
    }

   
    $(document).ready(function() {
      $.getJSON('/smob_pp/relationship.json', function ( data ) { 
        console.debug( data ); 
        for (rel in data) {
          if (rel.indexOf("http://purl.org/vocab/relationship/") === 0) {
          //if (rel.substring(0, "http://purl.org/vocab/relationship/".lenght) === "http://purl.org/vocab/relationship/") {
            //console.log(data[rel]['http://www.w3.org/2000/01/rdf-schema#label']);
            if (data[rel].hasOwnProperty("http://www.w3.org/2000/01/rdf-schema#label")) {
              var label = data[rel]['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
              var desc = data[rel]['http://www.w3.org/2004/02/skos/core#definition'][0]['value'];
              //var option_data =  "<option value='"+rel+"' label='"+desc+"'>"+label+ "</option>";
              var option_data =  "<option value='"+rel+"' >"+label+ "</option>";
              $('#relationships').append(option_data);           
            }
          }
        }
      });
      $("#generate-pp").click(function () {
        generate_pp();
      });
    });
  </script>
__END__;
    return $pp_js;
  }
  
  public function generate_pp($hashtag, $interest, $rel) {
    $pp = <<<__END__ 
http://mysite.org/preference/rdf a ppo:PrivacyPreference;
    ppo:appliesToResource 
     http://rdfs.org/sioc/ns#MicroblogPost;
    ppo:hasCondition [
      ppo:hasProperty tag:Tag;
      ppo:resourceAsObject 
       $hastag
    ];
   ppo:assignAccess acl:Read;
   ppo:hasAccessSpace [
     ppo:hasAccessQuery "SELECT ?user WHERE { 
  ?user foaf:topic_interest ?topic .
  ?topic dcterms:subject category:$interest .}"
  ] .
__END__;
		$graph = $this->graph();
		$rdf = SMOBTools::render_sparql_triples($pp);	
		$query = "INSERT INTO <$graph> { $rdf }";
		SMOBStore::query($query);
		print '<li>Privacy Preferences saved!';
  }
}
