<?php include('header.php'); ?>  

  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/dbpedia-spotlight-0.2.js"></script> 
  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/dbpedia-spotlight-0.1.js"></script> 
  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/private_profile.js"></script> 
  <script type="text/javascript"> 
    //var rel_types = get_rel_types();
    //console.debug(rel_types);


  //$(function() {
    //$('#interest_suggestion').dialog({ 
    //    autoOpen: false,
    //    height: 300,
    //    width: 350,
    //    modal: true,
    //    buttons: {
    //        "Ok": function() {
    //          $("#i").val() =  $("#suggestions :checked").val();
    //          $(this).dialog("close"); 
    //        }
    //    },
    //    close: function() {
    //      
    //    }
    //});
  //});

    $(document).ready(function(){
      
      $("#tabs_interest").tabs();
      $('#interest').focus(function() {
        $('.interest-details').show();
      });
      numwords_interest = 0;
      $('#interest').keyup(function(){
        interlink_interest('#interest', '#lod_interest', '#tabs_interest');
      });

      //set_rel_types('#rel_type');

      $('#add_rel').click(function(e) {
        e.preventDefault();
        addRel();
      });
      $('#add_interest').click(function(e) {
        e.preventDefault();
        addInterest();
      });

      $('#private_submit').click(function(e) {
        e.preventDefault();
        //$("#private_form").validate({
        // submitHandler: function(form) {
           //form.submit();
        //    var user_uri = document.location.href;
        //    user_uri.replace("/private","");
        //    post_private_profile(user_uri);
        // }
        //});
        //$("#private_form").validate();
        var user_uri = document.location.href;
        //user_uri.replace("/private","");
        //post_private_profile("https://localhost/smob/me/private");
        //post_private_profile("https://localhost/smob/me");
        post_data2triples("https://localhost/smob/me");
      });

    });
    
  </script>
<div id="main"> 
 
<div class="left">  
  
<h2>Private profile</h2>
    <form id="private_form">

      </br><b>Interests</b>

      <fieldset id="interest_fieldset"><legend>Interest</legend> 
        <textarea id="interest" name="interest" rows="1" cols="30"><?=$params['lod'];?></textarea> 
        <div class="interest-details" style="display: none;"> 
          <div id="lod_interest">Links will be suggested while typing ... (space required after each #tag)
            <div id="tabs_interest">
              <ul></ul>
            </div> 
          </div> 
        </div> 
      </fieldset> 

      <div id="interest_block">
        <?php foreach($params['interest_fieldsets'] as $fieldset): ?>
          <?=$fieldset;?>
        <?php endforeach; ?>
      </div> 
      <p><a id="add_interest" href="">[+]</a></p>

      <input type="hidden" id="interest_counter" value="<?=$params['interest_counter'];?>">

      </br><b>relationships</b>

      <select id="rel_type" name="rel_type" style="visibility:hidden;">
          <?=$params['rel_type_options'];?>
      </select>

      <div id="rel_block">
        <?php foreach($params['rel_fieldsets'] as $fieldset): ?>
          <?=$fieldset;?>
        <?php endforeach; ?>
      </div> 

      <p><a id="add_rel" href="">[+]</a></p>
       
      <input type="hidden" id="rel_counter" value="<?=$params['rel_counter'];?>">
       
      <button id="private_submit" class="content-details">Save</button>
    </form> 
 
<h2>Result</h2>
    <div id="result"></div>
    <div id="privacy_result" class="post external"></div>
 
</div> 
 

<?php include('nav.php'); ?>  
<?php include('footer.php'); ?> 
 
