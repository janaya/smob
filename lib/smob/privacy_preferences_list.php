<script type="text/javascript">

function del_pp(uri) {
  $.post("<?php echo SMOB_ROOT; ?>ajax/privacy_del.php?", {graph: uri}, function(data){
    console.debug(data);
    $("#result").html(data);
  });
  $("#"+uri).remove();
}
</script>

<h2>Privacy Settings List</h2>
    <form id="privacy_list">

      </br>
        <a href="<?=PRIVACY_PREFERENCES_ADD_URL_PATH ;?>" onclick="">Add privacy preference</a>
        <?php foreach($params['preferences'] as $item): ?>
        <div id="<?=$item['pp'];?>" typeof="ppo::PrivacyPreference"
        about="<?=$item['pp'];?>; ?>" class="post internal" cols="82"> 
            <?=$item['pp'];?>
        <!-- <span style="display:none;" rel="ppo:appliesToResource" href=""></span>-->
            <div id="hashtag_block">
              hashtag:  <?=$item['hashtag'];?>
              <!-- <?php foreach($item['hashtag'] as $hashtag_item): ?>
                <?=$hashtag_item;?>
              <?php endforeach; ?> -->
            </div>
            <div id="interest_block">
              access query: <?php echo str_replace('>', '&gt;', str_replace('<', '&lt;', $item['accessquery']));?>
              <?php error_log($item['accessquery']); ?>
              <!--  <?php foreach($item['accessquery'] as $accessquery_item): ?>
                <?=$accessquery_item;?>
              <?php endforeach; ?> -->
            </div>
        <a href="" onclick="del_pp('<?=$item['pp'];?>'); return false;">[-]</a>
        </div>
        <?php endforeach; ?>

      </br>

    </form>

