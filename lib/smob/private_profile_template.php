<?php include('header.php'); ?>  

  <script type="text/javascript"> 
    //var rel_types = get_rel_types();
    //console.debug(rel_types);

    $(document).ready(function(){
      $("#tabs_interest").tabs();
      $('#interest').focus(function() {
        $('.interest-details').show();
      });
      numwords_interest = 0;
      $('#interest').keyup(function(){
        interlink_interest('#interest', '#lod_interest', '#tabs_interest');
      });

      set_rel_types('#rel_type');

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
        //$("#private_form").validate({
        // submitHandler: function(form) {
           //form.submit();
        //    var user_uri = document.location.href;
        //    user_uri.replace("/private","");
        //    post_private_profile(user_uri);
        // }
        //});
        $("#private_form").validate();
        var user_uri = document.location.href;
        user_uri.replace("/private","");
        post_private_profile(user_uri);
      });

    });
    
  </script>
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
      
      <select id="rel_type" name="rel_type" style="visibility:hidden;"></select>
      
      <div id="rel_block">
      </div> 

      <p><a id="add_rel" href="">[+]</a></p>
       
      <input type="hidden" id="counter" value="0">
       
      <button id="private_submit" class="content-details">Save</button>
    </form> 
 
<h2>Result</h2>
    <div id="result"></div>
    <div id="privacy_result"></div>
 
</div> 
 

<?php include('nav.php'); ?>  
<?php include('footer.php'); ?> 
 
