<?php include('header.php'); ?>  

  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/private_profile.js"></script> 
  <script type="text/javascript"> 
    var smob_root = "<?php echo SMOB_ROOT; ?>";

    $(document).ready(function(){
      // TODO: add validation

      //set_rel_types('#rel_type');

      $('#add_rel').click(function(e) {
        e.preventDefault();
        addRel();
      });
      $('#add_interest').click(function(e) {
        e.preventDefault();
        var domids = {
          "topics_block" : "interest_form",
          "topic_block" : "interest_block",
          "topic_label" : "interest_label",
          "topic_interlink" : "interest_suggestion",
          "topic_uri" : "interest",
          "topic_interlink_form" : "interest_form",
          "topic_interlink_block" : "suggestions",
          "topic_interlink_submit" : "suggestion_submit",
          "topic_add" : "add_interest",
          "topic_del" : "del_interest",
          "topic_counter" : "interest_counter"
        }
        addInterest(smob_root, domids);
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
        post_data2triples(smob_root+"me");
      });

    });
    
  </script>
<div id="main"> 
 
<div class="left">  
  
<h2>Private profile</h2>
    <form id="private_form">

    </br>

      <fieldset><legend><b>Interests</b></legend>
        <div id="interest_block">
          <?php foreach($params['interest_fieldsets'] as $interest_item): ?>
            <?=$interest_item;?>
          <?php endforeach; ?>
        </div> 
        <p><a id="add_interest" href="">[+]</a></p>
        <input type="hidden" id="interest_counter" value="<?=$params['interest_counter'];?>">
      </fieldset>

    </br>

      <fieldset><legend><b>Relationships</b></legend>
        <select id="rel_type" name="rel_type" style="visibility:hidden;">
            <?=$params['rel_type_options'];?>
        </select>

        <div id="rel_block">
          <?php foreach($params['rel_fieldsets'] as $rel_item): ?>
            <?=$rel_item;?>
          <?php endforeach; ?>
        </div> 
        <p><a id="add_rel" href="">[+]</a></p>
        <input type="hidden" id="rel_counter" value="<?=$params['rel_counter'];?>">
      </fieldset>
      <button id="private_submit" class="content-details">Save</button>

    </form> 

<h2>Result</h2>
    <div id="result"></div>
    <div id="privacy_result" class="post external" style="display:none;"></div>
 
</div> 
 

<?php include('nav.php'); ?>  
<?php include('footer.php'); ?> 
 
