<?php include('header.php'); ?>  

  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/private_profile.js"></script> 
  <script type="text/javascript"> 


    $(document).ready(function(){
      // TODO: add validation

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
        post_data2triples("https://localhost/smob/me");
      });

    });
    
  </script>
<div id="main"> 
 
<div class="left">  
  
<h2>Private profile</h2>
    <form id="privacy_form">

      </br>

      <p><b>Condition</b></p>
      <fieldset><legend>Hashtag that the microposts must contain</legend> 
        <div id="hashtag_block">
          <?php foreach($params['hashtags'] as $hashtag_item): ?>
            <?=$hashtag_item;?>
          <?php endforeach; ?>
        </div> 
        <p><a id="add_hashtag" href="">[+]</a></p>
        <input type="hidden" id="hashtag_counter" value="<?=$params['hashtag_counter'];?>">
      </fieldset>

      </br>


      <p><b>Access Space </b></p>
      <fieldset><legend>Topics on which subcribers must be interested to receive the microposts</legend> 
        <div id="interest_block">
          <?php foreach($params['interest_fieldsets'] as $interest_item): ?>
            <?=$interest_item;?>
          <?php endforeach; ?>
        </div> 
        <p><a id="add_interest" href="">[+]</a></p>
        <input type="hidden" id="interest_counter" value="<?=$params['interest_counter'];?>">
      </fieldset>

      </br>

      <button id="private_submit" class="content-details">Save</button>

    </form> 

<h2>Result</h2>
    <div id="result"></div>
    <div id="profile_result" class="post external" style="display:none;"></div>
 
</div> 
 

<?php include('nav.php'); ?>  
<?php include('footer.php'); ?> 
 
