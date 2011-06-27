<?php
// TODO: Check authentication to avoid hijacking
$hashtag =  $GET['hashtag'];
$interest =  $GET['interest'];
$rel =  $GET['rel'];
SMOBPP::generate_pp($hashtag, $interest, $rel);
}
