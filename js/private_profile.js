function suggestion(domform, domterm, domuri) {
  var term = $(domterm).val();
  var loc = document.location.href;
  loc = loc.replace("private/edit","");
  console.debug(loc + "ajax/suggestions.php?");
  $.get(loc+"ajax/suggestions.php?type=tag&term="+urlencode(term)+getCacheBusterParam(), function(data){
    console.debug(data);
    var obj = JSON.parse(data);
    $(domform).append(data);
    $(domform).append(obj.html);
  });
}

function set_suggestion_popup(domterm, domform, dombutton, domformbutton) {
  var term = $(domterm).val();
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
  var rel_counter = parseInt($('#rel_counter').val());
  for(i=0; i<rel_counter; i++) {
    var person = $('#person'+i).val();
    var rel_type = $('#rel_type'+i).val();
    var rel_label = $('#rel_type'+i+' option:selected').text();
    //rel_persons[rel_type] = person;
    //rel_names[rel_type] = rel_label;
    triples = triples + "<" + user_uri + "> <" + rel_type + "> <" + person + "> . ";
    triples = triples + "<" + rel_type + "> <http://www.w3.org/2000/01/rdf-schema#label> <" + rel_label + "> . ";
  }
  //$.each(rel_persons, function(rel_type, person) { 
  var interest_counter = parseInt($('#interest_counter').val());
  for(i=0; i<interest_counter; i++) {
    var interest = $('#interest'+i).val();
    var interest_label = $('#interest'+i).attr('name');
    triples = triples + "<" + user_uri + "> <http://xmlns.com/foaf/0.1/topic_interest> <" + interest + "> . ";
    triples = triples + "<" + interest + "> <http://www.w3.org/2000/01/rdf-schema#label> <" + interest_label + "> . ";
  }
  
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
    triples = triples + "<" + lod_uri + "> <http://www.w3.org/2000/01/rdf-schema#label> <" + lod_label + "> . ";
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
  $('#rel_counter').val() = counter;
}
function addInterest() {
  var counter = parseInt($('#interest_counter').val());
  $("#interest_block").append("<fieldset id='interest_fieldset" + counter + "'><legend>interest</legend>          <input type='text' id='interes_label" + counter + "' name='interest_label" + counter + "' class='required' size='30' />           <a id='interest_suggestion" + counter + "' href='' onClick='suggestion(\"#interest_form" + counter + "\", \"#interest_label" + counter + "\", \"#interest" + counter + "\"); return false;'>Validate!</a>                      (<input name='interest" + counter + "' id='interest" + counter + "' type='text' class='url required' size='30' readonly />)           <a id='del_interest" + counter + "' href='' onClick='del(\"#interest_fieldset" + counter + "\"); return false;'>[-]</a>           <div id='interest_form" + counter + "' style='display: none;'></div>        </fieldset>");
  counter = counter + 1;
  parseInt($('#interest_counter').val());
}

//////////////////////////////////////////////////////////////////////////////

