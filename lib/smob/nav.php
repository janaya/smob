<?php require_once(dirname(__FILE__)."/../../config/config.php"); ?>
<div class="right"> 
 
<h2>Navigation</h2> 
<ul> 
<li><a href='<?php echo SMOB_ROOT; ?>'>Home</a></li> 
<li><a href='<?php echo MAP_URL; ?>'>Map view</a></li> 
<li><a href='<?php echo SPARQL_URL; ?>'>SPARQL</a></li> 
</ul> 
 
<h2>People</h2> 
<ul> 
<li><a href='<?php echo ME_URL; ?>'>Owner</a> [<a href='<?php echo ME_FEED_URL; ?>'>RSS</a>]</li> 
<li><a href='<?php echo FOLLOWINGS_URL; ?>'>Followings</a></li> 
<li><a href='<?php echo FOLLOWERS_URL; ?>'>Followers</a></li> 
<li><a href='<?php echo REPLIES_URL; ?>replies'>@replies</a></li> 
</ul> 
 
<h2>Hub owner</h2> 
<ul> 
<!-- TODO: Login and logout should be shown depending on the authentication state -->
<li><a href='<?php echo AUTH_URL; ?>'>Login</a></li> 
<li><a href='<?php echo LOGOUT_URL; ?>'>Logout</a></li>
<li><a href='<?php echo PRIVACY_PREFERENCES_URL; ?>'>Privacy Settings</a></li> 
<li><a href='<?php echo PRIVATE_PROFILE_EDIT_URL; ?>'>Private Profile Editor</a></li> 
</ul> 
  
</div> 
