
<script type="text/javascript">

function del_pp(id) {
//  console.data(id);
  var uri = $(id).attr("about");
  var graph = uri.replace('privacy', 'ppo');
  $.post("<?php echo SMOB_ROOT; ?>ajax/privacy_del.php?", {graph: graph}, function(data){
    console.debug(data);
    $("#result").html(data);
  });
//  $('#privacy_list').remove('#'+jq(uri));
  $('#privacy_list').remove($(id));
  $(id).remove();
}

//$("#adel").click(function () { 
//  var uri = $(this).parent().attr("about"); 
//  var graph = uri.replace('privacy', 'ppo');
//  $.post("<?php echo SMOB_ROOT; ?>ajax/privacy_del.php?", {graph: graph}, function(data){
//    console.debug(data);
//    $("#result").html(data);
//  });
//  $('#privacy_list').remove($(this).parent());
//});
</script>

<h2>Privacy Settings List</h2>
    <form id="privacy_list">

      </br>
        <?php $counter=0; ?>
        <?php foreach($params['preferences'] as $item): ?>
            <!--<div id="<?php echo str_replace(array('.',':', '|'), array('\.','\:','\|'), $item['pp']);?>" typeof="ppo::PrivacyPreference"
            about="<?=$item['pp'];?>" class="post internal" cols="82"> -->
            <?php $preferenceid = '#preference'.$counter;?>
            <div id="<?='preference'.$counter;?>" typeof="ppo::PrivacyPreference"
            about="<?=$item['pp'];?>" class="post internal" cols="82">
                <?=$item['pp'];?>
                <!-- <span style="display:none;" rel="ppo:appliesToResource" href=""></span>-->
                <div id="hashtag_block">
                  <b>condition</b>:  <?php echo str_replace('>', '&gt;', str_replace('<', '&lt;', $item['hashtag']));?>
                  <!-- <?php foreach($item['hashtag'] as $hashtag_item): ?>
                    <?=$hashtag_item;?>
                  <?php endforeach; ?> -->
                </div>
                <div id="interest_block">
                  <b>access query</b>: <?php echo str_replace('>', '&gt;', str_replace('<', '&lt;', $item['accessquery']));?>
                  <?php error_log($item['accessquery'],0); ?>
                </div>
                <!--<a href="" onclick="del_pp('<?=$item['pp'];?>'); return false;">[-]</a>-->
                <!--<a id="adel" href="" >[-]</a>-->
                <a href="" onclick="del_pp('<?=$preferenceid;?>'); return false;">[-]</a>
            </div>
            <?php $counter+=1;?>
        <?php endforeach; ?>


      </br>

    </form>

