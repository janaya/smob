
function set_suggestion_popup(domterm, domform, dombutton, domformbutton) {
  var term = $(domterm).val();
  console.debug(term);
  var loc = document.location.href;
  loc = loc.replace("private/edit","");
  console.debug(loc + "ajax/suggestions.php?");
  $.get(loc+"ajax/suggestions.php?type=tag&term="+urlencode(term)+getCacheBusterParam(), function(data){
    console.debug(data);
    $(domform).append(data);
  });
  $(dombutton).remove();
  $(domformbutton).show();
}

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

function post_data2triples(user_uri) {
  //var rel_names = [];
 // var rel_persons = [];
  var triples = "";
  var rel_counter = parseInt($('#rel_counter').val());
  for(i=0; i<rel_counter; i++) {
    var person = $('#person'+i).val();
    var rel_type = $('#rel_type'+i).val();
    var rel_label = $('#rel_type'+i+' option:selected').text();
    //rel_persons[rel_type] = person;
    //rel_names[rel_type] = rel_label;
    triples = triples + "<" + user_uri + "> <" + rel_type + "> <" + person + "> . ";
    triples = triples + "<" + rel_type + "> <http://www.w3.org/2000/01/rdf-schema#label> '" + rel_label + "' . ";
  }
  //$.each(rel_persons, function(rel_type, person) { 
  var interest_counter = parseInt($('#interest_counter').val());
  for(i=0; i<interest_counter; i++) {
    var interest = $('#interest'+i).val();
    var interest_label = $('#interest'+i).attr('name');
    triples = triples + "<" + user_uri + "> <http://xmlns.com/foaf/0.1/topic_interest> <" + interest + "> . ";
    triples = triples + "<" + interest + "> <http://www.w3.org/2000/01/rdf-schema#label> '" + interest_label + "' . ";
  }
  console.debug(triples);
  
  $("#privacy_result").text(triples).html();
  var loc = document.location.href;
  loc = loc.replace("private/edit","");
  console.debug(loc + "ajax/private.php?" + $.param({"triples":triples}));
  $.post(loc + "ajax/private.php?", {triples:triples}, function(data){
    console.debug(data);
    $("#result").html(data);
  });
}

function post_private_profile(user_uri) {
  var persons = [];
  var rel_types = [];
  var triples = "";
  $.each($('#private_form').serializeArray(), function(i, field) {
      var p = field.name.replace( "person", "");
      if (p.length < 3 && p.length > 0) {
        persons[parseInt(p)] = field.value;    
      } else {
        var t = field.name.replace( "rel_type", "");
        if (t.length < 3 && t.length > 0) {
          rel_types[parseInt(t)] = field.value;    
        }  
      }
  });
  console.debug(persons);
  console.debug(rel_types);
  
  var triples = "";
  for(i=0; i<persons.length; i++) {
    triples = triples + "<" + user_uri + "> <" + rel_types[i] + "> <" + persons[i] + "> . ";
  }
  console.debug(triples);
  
  $("#lod_interest :checked").each(function() {
    lod_uri = $(this).val().split('--')[2];
    lod_label = $(this).val().split('--')[1];
    triples = triples + "<" + user_uri + ">  <http://xmlns.com/foaf/0.1/topic_interest> <" + lod_uri + "> . ";
    triples = triples + "<" + lod_uri + "> <http://www.w3.org/2000/01/rdf-schema#label> '" + lod_label + "' . ";
  })
  console.debug(triples);
  
  $("#privacy_result").text(triples).html();
  var loc = document.location.href;
  loc = loc.replace("private/edit","");
  console.debug(loc + "ajax/private.php?" + $.param({"triples":triples}));
  $.post(loc + "ajax/private.php?", {triples:triples}, function(data){
    console.debug(data);
    $("#result").html(data);
  });
}

function del(domid) {
  $(domid).remove();
}
function addRel() {
  var counter = parseInt($('#rel_counter').val());
  $("#rel_block").append("<fieldset id='rel_fieldset" + counter + "'><legend>Relationship</legend>           <select id='rel_type" + counter + "' name='rel_type" + counter + "' class='required'>           </select>           <input name='person" + counter + "' id='person" + counter + "' type='text' class='url required' size='30' />           <a id='del_rel" + counter + "' href='' onClick='del(\"#rel_fieldset" + counter + "\"); return false;'>[-]</a>        </fieldset>");
  $('#rel_type').children().clone().appendTo('#rel_type' + counter);
  counter = counter + 1;
  $('#rel_counter').val(counter);
}

function addInterest() {
  var i = parseInt($('#interest_counter').val());
  var interest_block = "        <fieldset id='interest_fieldset" + i + "'><legend>interest</legend>";
  interest_block = interest_block + "          <input type='text' id='interest_label" + i + "' name='interest_label" + i + "' class='required' size='30' />";
  interest_block = interest_block + "          <a id='interest_suggestion" + i + "' href='' onClick='suggestion(\"#interest_form" + i + "\", \"#suggestions" + i + "\", \"#interest_label" + i + "\"); return false;'>Validate!</a>";
  interest_block = interest_block + "          (<input name='interest" + i + "' id='interest" + i + "' type='text' class='url required' size='30' readonly />)";
  interest_block = interest_block + "          <a id='del_interest" + i + "' href='' onClick='del(\"#interest_fieldset" + i + "\"); return false;'>[-]</a>";
  interest_block = interest_block + "          <div id='interest_form" + i + "' style='display: none;'>";
  interest_block = interest_block + "            <div id='suggestions" + i + "'></div>";
  interest_block = interest_block + "            <a id='suggestion_submit" + i + "' href='' onClick='suggestion_submit(\"#interest_form" + i + "\", \"#interest" + i + "\", \"#interest_label" + i + "\); return false;'>Done!</a>";
  interest_block = interest_block + "          </div>";
  interest_block = interest_block + "        </fieldset>";
  $("#interest_block").append(interest_block);
  i = i + 1;
 $('#interest_counter').val(i);
}

function suggestion_submit(domform, domuri, domlabel) {
  //var suggestion = $('suggestion option:selected').text();
  var suggestion = $('input:radio[name=suggestion]:checked').val();
  var suggestion_label = $('input:radio[name=suggestion]:checked + label').text();
  console.debug(suggestion);
  $(domuri).val(suggestion);
  $(domlabel).val(suggestion_label);
  $(domform).hide();
}

function suggestion(domform, domsuggestions, domterm) {
  var term = $(domterm).val();
  var loc = document.location.href;
  loc = loc.replace("private/edit","");
  console.debug(loc + "ajax/suggestions.php?type=tag&term="+urlencode(term)+getCacheBusterParam());
  $.get(loc+"ajax/suggestions.php?type=tag&term="+urlencode(term)+getCacheBusterParam(), function(data){
    console.debug(data);
    $(domsuggestions).append(data);
    $(domform).show();
  });
}

