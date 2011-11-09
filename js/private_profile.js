//function set_suggestion_popup(domterm, domform, dombutton, domformbutton) {
//  var term = $(domterm).val();
//  console.debug(term);
//  var loc = document.location.href;
//  loc = loc.replace("private/edit","");
//  console.debug(loc + "ajax/suggestions.php?");
//  $.get(loc+"ajax/suggestions.php?type=tag&term="+urlencode(term)+getCacheBusterParam(), function(data){
//    console.debug(data);
//    $(domform).append(data);
//  });
//  $(dombutton).remove();
//  $(domformbutton).show();
//}

function set_rel_types(domid) {
  var option_data =  "<option value='' selected=''></option>";
  $(domid).append(option_data);
  $.getJSON('/smob/relationship.json', function ( data ) { 
    for (rel in data) {
      if (rel.indexOf("http://purl.org/vocab/relationship/") === 0) {
        if (data[rel].hasOwnProperty("http://www.w3.org/2000/01/rdf-schema#label")) {
          var label = data[rel]['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
          var option_data =  "<option name='" + label + "' value='"+rel+"' >"+label+ "</option>";
          $(domid).append(option_data);
        }
      }
    }
  });
}

//function set_rel_types(domid, rel_types) {
//  for (label in rel_types) {
//    var option_data =  "<option value='"+label+ "' value='"+rel_types[label]+"' >"+label+ "</option>";
//    $(domid).append(option_data);
//  }
//}

//function get_rel_types() {
//  var rels = {};
//  $.getJSON('/smob/relationship.json', function ( data ) { 
//    for (rel in data) {
//      if (rel.indexOf("http://purl.org/vocab/relationship/") === 0) {
//        if (data[rel].hasOwnProperty("http://www.w3.org/2000/01/rdf-schema#label")) {
//          var label = data[rel]['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
//          rels[label] = rel;
//        }
//      }
//    }
//  return rels;
//  });
//};

function del(domid) {
  $(domid).remove();
}
function addRel() {
  var i = parseInt($('#rel_counter').val());
  var rel_block = "<div id='rel_fieldset"+i+"'>";
  rel_block = rel_block + "           <select id='rel_type"+i+"' name='rel_type"+i+"' class='required'>";
  rel_block = rel_block + "           </select>";
  rel_block = rel_block + "           <input name='person"+i+"' id='person"+i+"' type='text' class='url required' size='50' />";
  rel_block = rel_block + "           <a id='del_rel"+i+"' href='' onClick='del(\"#rel_fieldset"+i+"\"); return false;'>[-]</a>";
  rel_block = rel_block + "        </div></br>";
  $("#rel_block").append(rel_block);
  $('#rel_type').children().clone().appendTo('#rel_type' + i);
  i = i + 1;
  $('#rel_counter').val(i);
}

function addTopic(smob_root, topic_domids) {
  var i = parseInt($('#'+topic_domids.topic_counter).val());
  
  var topic_block = "<div id='"+topic_domids.topic_block+i+"'>";
  topic_block += "  <input type='text' id='"+topic_domids.topic_label+i+"' name='"+topic_domids.topic_label+i+"' class='required' size='20' />";
  topic_block += "  <a id='"+topic_domids.topic_interlink+i+"' href='' onClick='suggestion(smob_root,"+topic_domids.label+"); return false;'>Interlink!</a>";
  topic_block += "  (<input name='"+topic_domids.topic_uri+i+"' id='"+topic_domids.topic_uri+i+"' type='text' class='url required' size='40' readonly />)";
  topic_block += "  <a id='"+topic_domids.topic_del+i+"' href='' onClick='del(\"#"+topic_domids.topic_block+i+"\"); return false;'>[-]</a>";
  topic_block += "  <div id='"+topic_domids.topic_interlink_form+i+"' style='display: none;'>";
  topic_block += "    <div id='"+topic_domids.topic_interlink_block+i+"'></div>";
  topic_block += "    <a id='"+topic_domids.topic_interlink_submit+i+"' href='' onClick='suggestion_submit("+topic_domids.label+"); return false;'>Done!</a>";
  topic_block += "  </div>";
  topic_block += "</div></br>";
  $("#"+topic_domids.topics_block).append(topic_block);
}

function set_suggestion_result(data, topic_domids){
  var i = parseInt($('#'+topic_domids.topic_counter).val());
  $('#'+topic_domids.topic_interlink_block+i).children().remove();
  $('#'+topic_domids.topic_interlink_block+i).append(data);
  $('#'+topic_domids.topic_interlink_form+i).show();
}

function suggestion(smob_root, topic_domids) {
  var i = parseInt($('#'+topic_domids.topic_counter).val());
  var term = $('#'+topic_domids.topic_label+i).val();
  //var loc = document.location.href;
  //loc = loc.replace("private/edit","");
  console.debug(smob_root + "ajax/suggestions.php?type=tag&term="+urlencode(term)+getCacheBusterParam());
  $.get(smob_root + "ajax/suggestions.php?type=tag&term="+urlencode(term)+getCacheBusterParam(), function(data){
    console.debug(data);
    set_suggestion_result(data, topic_domids);
  });
}

function suggestion_submit(topic_domids) {
  var i = parseInt($('#'+topic_domids.topic_counter).val());
  //var suggestion = $('suggestion option:selected').text();
  var suggestion = $('input:radio[name=suggestion]:checked').val();
  var id = $('input:radio[name=suggestion]:checked').attr('id');
  var suggestion_label = $("label[for="+id+"]").text();
  console.debug(suggestion);
  $('#'+topic_domids.topic_uri+i).val(suggestion);
  $('#'+topic_domids.topic_label+i).val(suggestion_label);
  $('#'+topic_domids.topic_interlink_block+i).children().remove();
  $('#'+topic_domids.topic_interlink_form+i).hide();
  i += 1;
  $('#'+topic_domids.topic_counter).val(i);
}

function post_data2triples(user_uri) {
  var triples = "";
  var rel_counter = parseInt($('#rel_counter').val());
  for(i=0; i<rel_counter; i++) {
    var person = $('#person'+i).val();
    var rel_type = $('#rel_type'+i).val();
    var rel_label = $('#rel_type'+i+' option:selected').text();
    if ((rel_type != undefined) && (rel_label != undefined) && (person != undefined)) {
    triples = triples + "<" + user_uri + "> <" + rel_type + "> <" + person + "> . ";
      triples = triples + "<" + rel_type + "> <http://www.w3.org/2000/01/rdf-schema#label> '" + rel_label + "' . ";
      }
  }
  var interest_counter = parseInt($('#interest_counter').val());
  for(i=0; i<interest_counter; i++) {
    var interest = $('#interest'+i).val();
    var interest_label = $('#interest_label'+i).val();
    // we will never have an id biggest than counter, but it could happen that some of the items where removed
    if ((interest != undefined) && (interest_label != undefined)) {
      triples = triples + "<" + user_uri + "> <http://xmlns.com/foaf/0.1/topic_interest> <" + interest + "> . ";
      triples = triples + "<" + interest + "> <http://www.w3.org/2000/01/rdf-schema#label> '" + interest_label + "' . ";
    }
  }
  console.debug(triples);
  
  $("#privacy_result").text(triples).html();
  $("#privacy_result").show();
  var loc = document.location.href;
  loc = loc.replace("private/edit","");
  console.debug(loc + "ajax/private.php?" + $.param({"triples":triples}));
  $.post(loc + "ajax/private.php?", {triples:triples}, function(data){
    console.debug(data);
    $("#result").html(data);
  });
}


function post_privacydata2triples(smob_root) {
  var accessspace = "";
  
  var rel_counter = parseInt($('#rel_counter').val());
  for(i=0; i<rel_counter; i++) {
    var person = $('#person'+i).val();
    var rel_type = $('#rel_type'+i).val();
//    var rel_label = $('#rel_type'+i+' option:selected').text();
//    if ((rel_type != undefined) && (rel_label != undefined) && (person != undefined)) {
    if ((rel_type != undefined) && (person != undefined)) {
    accessspace += "?user <" + rel_type + "> <" + person + "> . ";
      }
  }
  
  var interest_counter = parseInt($('#interest_counter').val());
  for(i=0; i<interest_counter; i++) {
    var interest = $('#interest'+i).val();
    var interest_label = $('#interest_label'+i).val();
    // we will never have an id biggest than counter, but it could happen that some of the items where removed
    if ((interest != undefined) && (interest_label != undefined)) {
      accessspace += "?user foaf:topic_interest <" + interest + "> . ";
      //accessspace += "<" + interest + "> <http://www.w3.org/2000/01/rdf-schema#label> '" + interest_label + "' . ";
                  //foaf:knows
                  //filter
                  // ?topic dcterms:subject category:Semantic_Web
    }
  }
  console.debug(accessspace);
  var accessquery = "SELECT ?user WHERE { " + accessspace + "}";

  var resource_object = "";
  var hashtag_counter = parseInt($('#hashtag_counter').val());
  for(i=0; i<hashtag_counter; i++) {
    var hashtag = $('#hashtag'+i).val();
    var hashtag_label = $('#hashtag_label'+i).val();
    // we will never have an id biggest than counter, but it could happen that some of the items where removed
    if ((hashtag != undefined) && (hashtag_label != undefined)) {
      resource_object += " ;  ppo:resourceAsObject <" + hashtag + ">";
                    //filter?
    }
  }
  console.debug(resource_object);
  
  var conditions = " ppo:hasProperty moat:taggedWith " + resource_object;
  var d = new Date();
  
  var graph = smob_root+'ppo/'+toISOString(d);
  var ppo = smob_root+'privacy/'+toISOString(d);
  var triples = "<"+ppo+"> a ppo:PrivacyPreference;";
  triples += "ppo:appliesToResource rdfs:MicroblogPost ;";
  triples += "ppo:assignAccess acl:Read ;";
  triples += "ppo:hasCondition [ " + conditions + "] ;";
  triples += "ppo:hasAccessSpace [ ppo:hasAccessQuery \"" + accessquery +"\"^^xsd:string ] . ";

//  query = "INSERT INTO <" + graph + "> { " + triples + " }";

//  $("#privacy_result").text(triples).html();
//  $("#privacy_result").show();
  //console.debug(smob_root + "ajax/privacy.php?" + $.param({"triples":triples}));
  //console.debug(smob_root + "sparql?" + $.param({"query":query}));
  $.post(smob_root + "ajax/privacy.php?", {graph: graph, triples:triples}, function(data){
  //$.post(smob_root + "sparql?", {query:query}, function(data){
    console.debug(data);
    $("#result").html(data);
  });
  add_privacy_item_to_list(ppo, conditions, accessquery);
}

function add_privacy_item_to_list(ppo, conditions, accessquery) {  
  var privacy_block = "<div id='" +jq(ppo)+"' typeof='ppo::PrivacyPreference' about='"+ppo+"' class='post internal' cols='82''> "+ppo;
  privacy_block = privacy_block + "<div id='hashtag_block'> hashtag:  "+$('<div/>').text(conditions).html()+ "</div>";
  privacy_block = privacy_block + "<div id='interest_block'> access query: "+$('<div/>').text(accessquery).html()+"</div>";
  privacy_block = privacy_block + "<a href='' onclick='del_pp(\"#"+ppo+"\"); return false;'>[-]</a>";
  privacy_block = privacy_block + "</div>";
  $("#privacy_list").prepend(privacy_block).fadeIn("slow");
//  $('<div/>').html(privacy_block).text()

}

function padzero(n) {
    return n < 10 ? '0' + n : n;
}
function pad2zeros(n) {
    if (n < 100) {
      n = '0' + n;
    }
    if (n < 10) {
      n = '0' + n;
    }
    return n;     
}
function toISOString(d) {
    return d.getUTCFullYear() + '-' +  padzero(d.getUTCMonth() + 1) + '-' + padzero(d.getUTCDate()) + 'T' + padzero(d.getUTCHours()) + ':' +  padzero(d.getUTCMinutes()) + ':' + padzero(d.getUTCSeconds()) + '.' + pad2zeros(d.getUTCMilliseconds()) + 'Z';
}

function jq(myid) { 
    return myid.replace(/(:|\.)/g,'\\$1');
}

function unjq(myid) { 
    return myid.replace(/(\\)/g,'$1');
}
