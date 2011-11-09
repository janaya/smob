  <script type="text/javascript">
    var smob_root = "<?php echo SMOB_ROOT; ?>";
    var interest_domids = {
      "label" : "interest_domids",
      "topics_block" : "interest_block",
      "topic_block" : "interest_fieldset",
      "topic_label" : "interest_label",
      "topic_interlink" : "interest_suggestion",
      "topic_uri" : "interest",
      "topic_interlink_form" : "interest_form",
      "topic_interlink_block" : "interest_suggestions",
      "topic_interlink_submit" : "interest_suggestion_submit",
      "topic_add" : "add_interest",
      "topic_del" : "del_interest",
      "topic_counter" : "interest_counter"
    }
    var hashtag_domids = {
      "label" : "hashtag_domids",
      "topics_block" : "hashtag_block",
      "topic_block" : "hashtag_fieldset",
      "topic_label" : "hashtag_label",
      "topic_interlink" : "hashtag_suggestion",
      "topic_uri" : "hashtag",
      "topic_interlink_form" : "hashtag_form",
      "topic_interlink_block" : "hashtag_suggestions",
      "topic_interlink_submit" : "hashtag_suggestion_submit",
      "topic_add" : "add_hashtag",
      "topic_del" : "del_hashtag",
      "topic_counter" : "hashtag_counter"
    }
  </script>
  <script type="text/javascript" src="<?php echo SMOB_ROOT; ?>js/private_profile.js"></script>
  <script type="text/javascript">
    var smob_root = "<?php echo SMOB_ROOT; ?>";
    // TODO: eliminate function arguments already defined as global vars

    $(document).ready(function(){
      // TODO: add validation

      //set_rel_types('#rel_type');

      $('#add_rel').click(function(e) {
        e.preventDefault();
        addRel();
      });
      $('#add_interest').click(function(e) {
        e.preventDefault();
        addTopic(smob_root, interest_domids);
      });
      $('#add_hashtag').click(function(e) {
        e.preventDefault();
        addTopic(smob_root, hashtag_domids);
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
        post_privacydata2triples(smob_root);
      });

    });

  </script>

<h2>Privacy Settings</h2>
    <form id="privacy_form">

      </br>

      <p><b>Condition</b></p>
      <fieldset><legend>Hashtag that the microposts must contain</legend>
        <div id="hashtag_block">
        </div>
        <p><a id="add_hashtag" href="">[+]</a></p>
        <input type="hidden" id="hashtag_counter" value="<?=$params['hashtag_counter'];?>">
      </fieldset>

      </br>


      <p><b>Access Space </b></p>
      <fieldset><legend>Topics on which the subcribers must be interested in to receive the microposts</legend>
        <div id="interest_block">
        </div>
        <p><a id="add_interest" href="">[+]</a></p>
        <input type="hidden" id="interest_counter" value="<?=$params['interest_counter'];?>">
      </fieldset>

      OR<input type="radio"  name="interest_rel" value="0" checked="checked"/> AND <input type="radio" name="interest_rel" value="1" />


      <fieldset><legend>Relationships the subcribers must have with me to receive the microposts</legend>
        <select id="rel_type" name="rel_type" style="visibility:hidden;">
            <?=$params['rel_type_options'];?>
        </select>
        <div id="rel_block">
        </div>
        <p><a id="add_rel" href="">[+]</a></p>
        <input type="hidden" id="rel_counter" value="<?=$params['rel_counter'];?>">
      </fieldset>

      </br>

      <button id="private_submit" class="content-details">Save</button>

    </form>

<h2>Result</h2>
    <div id="result"></div>
    <div id="privacy_result" class="post external" style="display:none;"></div>

