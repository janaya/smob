<?php require_once(dirname(__FILE__)."/../../config/config.php"); ?>
<div class="right"> 
 
<h2>Navigation</h2> 
<ul> 
<li><a href='<?php echo SMOB_ROOT; ?>'>Home</a></li> 
<li><a href='<?php echo SMOB_ROOT; ?>map'>Map view</a></li> 
<li><a href='<?php echo SMOB_ROOT; ?>sparql'>SPARQL</a></li> 
</ul> 
 
<h2>People</h2> 
<ul> 
<li><a href='<?php echo SMOB_ROOT; ?>me'>Owner</a> [<a href='<?php echo SMOB_ROOT; ?>me/rss'>RSS</a>]</li> 
<li><a href='<?php echo SMOB_ROOT; ?>followings'>Followings</a></li> 
<li><a href='<?php echo SMOB_ROOT; ?>followers'>Followers</a></li> 
<li><a href='<?php echo SMOB_ROOT; ?>replies'>@replies</a></li> 
</ul> 
 
<h2>Hub owner</h2> 
<ul> 
<!--> TODO: Login and logout should be shown depending on the authentication state<-->
<li><a href='<?php echo SMOB_ROOT; ?>auth'>Login</a></li> 
<li><a href='<?php echo SMOB_ROOT; ?>logout'>Logout</a></li>
<li><a href='<?php echo SMOB_ROOT; ?>pp'>Privacy Settings</a></li> 
<li><a href='<?php echo SMOB_ROOT; ?>private/edit'>Private Profile Editor</a></li> 
</ul> 
  
</div> 
