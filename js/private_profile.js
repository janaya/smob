function set_rel_types(domid) {
  var option_data =  "<option value='' selected=''></option>";
  $(domid).append(option_data);
  $.getJSON('/smob/relationship.json', function ( data ) { 
    for (rel in data) {
      if (rel.indexOf("http://purl.org/vocab/relationship/") === 0) {
        if (data[rel].hasOwnProperty("http://www.w3.org/2000/01/rdf-schema#label")) {
          var label = data[rel]['http://www.w3.org/2000/01/rdf-schema#label'][0]['value'];
          var option_data =  "<option value='"+rel+"' >"+label+ "</option>";
          $(domid).append(option_data);
        }
      }
    }
  });
}

//function set_rel_types(domid, rel_types) {
//  for (label in rel_types) {
//    var option_data =  "<option value='"+rel_types[label]+"' >"+label+ "</option>";
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

function post_private_profile(user_uri) {
  var persons = [];
  var rel_types = [];
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
    lod_uri = $(this).val().split('--')[1];
    lod_label = $(this).val().split('--')[2];
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

function delRel(id) {
  $(id).remove();
}
function addRel() {
  var counter = parseInt(document.getElementById("counter").value);
  $("#rel_block").append("<fieldset id='rel_fieldset" + counter + "'><legend>Relationship</legend>           <select id='rel_type" + counter + "' name='rel_type" + counter + "' class='required'>           </select>           <input name='person" + counter + "' id='person" + counter + "' type='text' class='url required' size='30' />           <a id='del_rel" + counter + "' href='' onClick='delRel(\"#rel_fieldset" + counter + "\"); return false;'>[-]</a>        </fieldset>");
  $('#rel_type').children().clone().appendTo('#rel_type' + counter);
  counter = counter + 1;
  document.getElementById("counter").value = counter;
}

//////////////////////////////////////////////////////////////////////////////

