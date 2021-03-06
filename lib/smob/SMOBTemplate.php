<?php

/*
    Deals with all the rendering aspect, from the complete application to individual posts
*/

class SMOBTemplate {

    public function publisher_header($reply_of = null) {
        error_log("Template::publisher_header",0);
        $contentblock = $reply_of ? "$('.content-details').show();" : "
        $('#content').focus(function() {
            $('.content-details').show();
        });";

        $form_js = <<<__END__
        <script type="text/javascript">
        $(document).ready(function() {
            $("#publish").click(function () {
                publish();
            });
            $contentblock
            numwords = 0;
            // XXX form.blur doesn't work :-/
            $('#content-form').blur(function() {
                if ($('#content').val().length == 0) {
                    $('.content-details').hide();
                }
            });
            $('#content').keyup(function(){
                interlink('#content', '#lod-form', '#tabs');
                charsleft();
            });
        });
        </script>
__END__;
        $form = '<h2>What&apos;s on your mind?</h2>';
        if($reply_of) {
                $r = explode('/', $reply_of);
                if($r[2] == 'twitter.com') {
                    $reply = '@'.$r[3].' ';
                }
                $len = 140 - strlen($reply);
                $form .= "<p>You are replying to post <a href='$reply_of'>$reply_of</a></p>";
                $form .= "<input type='hidden' name='reply_of' id='reply_of' value='$reply_of' />";
        } else {
                $len = 140;
                $form .= "<input type='hidden' name='reply_of' id='reply_of'/>";
        }
        $form .= '
            <span class="content-details" style="display: none;">
            (You have <span id="charsleft">' . $len . '</span> characters left)
            </span>
            <form id="content-form">
            <textarea name="content" id="content" rows="5" cols="82">' . $reply. '</textarea>
            <div class="content-details" style="display: none;">
';
        if($loc = SMOBTools::location()) {
            $location_uri = 'value ="'.$loc[0].'"';
            $location = 'value ="'.$loc[1].'"';
        }
$form .= '
            <fieldset><legend>Current location</legend>
            <input type="text" name="location" id="location" class="autocomplete" '.$location.'/>
            <input type="hidden" name="location_uri" id="location_uri" '.$location_uri.'/>
            </fieldset>
';
$form .= '
            <fieldset><legend>Interlinking</legend>
            <div id="lod-form">Links will be suggested while typing ... (space required after each #tag)
                <div id="tabs"><ul></ul></div>
            </div>
            </fieldset>
';

        $form .= '<fieldset><legend>Broadcast</legend>';
        if(TWITTER_POST) {
            $form .= "<input type='checkbox' name='twitter' id='twitter' checked='true'/>Twitter as ".TWITTER_USER."<br/>";
        }
        $form .= "<input type='checkbox' name='sindice' id='sindice' checked='true'/>Ping Sindice<br/>";
        $form .= '</fieldset>';

        $form .= '
            </div>
            </form>

            <button id="publish" class="content-details" style="display: none;">SMOB it!</button>

            <div id="smob-publish" style="display: none;">
                <br/><em>Publishing content ...</em>
            </div>
';
        return array($form_js, $form);
    }

    public function header($publisher, $reply_of = null, $ismap = null) {
        error_log("Template::header",0);
        global $type;
        //if(!defined('SMOB_ROOT')) {
        if(!defined('SMOB_ROOT')) {
            define('SMOB_ROOT', '');
        }
        if($publisher) {
            list($form_js, $form) = SMOBTemplate::publisher_header($reply_of);
        }
        if($ismap) {
            // GMap hack - FIXME !!
            echo '<br/>';
        }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN"
     "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:foaf="http://xmlns.com/foaf/0.1/"
    xmlns:sioc="http://rdfs.org/sioc/ns#"
    xmlns:sioct="http://rdfs.org/sioc/types#"
    xmlns:ctag="http://commontag.org/ns#"
    xmlns:opo="http://online-presence.net/opo/ns#"
    xmlns:smob="http://smob.me/ns#"
    xmlns:moat="http://moat-project.org/ns#"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:rev="http://purl.org/stuff/rev#"
xml:lang="fr">

<head profile="http://ns.inria.fr/grddl/rdfa/">
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <title>SMOB</title>
  <link rel="icon" type="image/png" href="<?php echo IMG_URL; ?>smob-icon.png" />
  <link rel="stylesheet" type="text/css" href="<?php echo CSS_URL; ?>style.css" />

<!--
  <link type="text/css" href="http://jqueryui.com/latest/themes/base/jquery.ui.all.css" rel="stylesheet" />

  <script type="text/javascript" src="http://www.google.com/jsapi"></script>
  <script type="text/javascript">
    google.load("jquery", "1.4.1");
    google.load("jqueryui", "1.7.2");
  </script>
--> 

  <script type="text/javascript" src="<?php echo JS_URL; ?>jquery.js"></script>
  <script type="text/javascript" src="<?php echo JS_URL; ?>jquery-ui.min.js"></script>
  <script type="text/javascript" src="<?php echo CSS_URL; ?>jquery.ui.all.css"></script>
  
  <script type="text/javascript" src="<?php echo JS_URL; ?>jquery.timers-1.2.js"></script>
  <script type="text/javascript" src="<?php echo JS_URL; ?>jquery.autocomplete-min.js"></script>
  <script type="text/javascript" src="<?php echo JS_URL; ?>jquery.rating.js"></script>

  
  <script type="text/javascript" src="<?php echo JS_URL; ?>smob.js"></script>

  <base href="<?php echo $root; ?>" />
  <script type="text/javascript">
    var state = 0;
    var maxstate = 6;
    $(function() {
        $("#step").click(function () {
            process();
        });
    });
    //$(function() {
//        $("#np").everyTime(10000,function(i) {
//            getnews();
//        });
//    });
    $(document).ready(function(){
        $("#tabs").tabs();
        <?php if($ismap) { echo "\n\nmap();"; } ?>
    });
  </script>
  <?php echo $form_js; ?>
</head>

<body about="<?php echo SMOB_ROOT; ?>" typeof="smob:Hub sioct:Microblog">

<div id="full">

<div id="header">
<h1><a href="<?php echo SMOB_ROOT; ?>">SMOB</a></h1>
<h2><span class="smob">S</span>emantic-<span class="smob">M</span>icr<span class="smob">OB</span>logging</h2>
</div>

<div id="main">

<div class="left">

<?php echo $form; ?>

<?php
    }

    public function footer() {
        error_log("Template::footer",0);
        $version = SMOBTools::version();
?>
</div>

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

<div style="clear: both;"> </div>
</div>

<div id="footer">
Powered by <a href="http://smob.me/">SMOB</a> <?php echo $version; ?> thanks to <a href="http://www.w3.org/2001/sw/">Semantic Web</a> and <a href="http://linkeddata.org">Linked Data</a> technologies.<br/>
This page is valid <a href="http://validator.w3.org/check?uri=referer">XHTML</a> and <a href="http://www.w3.org/2007/08/pyRdfa/extract?uri=referer">contains RDFa markup</a>.
<br/>
</div>

</div>

<script type='text/javascript'>
var options, a;
jQuery(function(){
    options = {
        serviceUrl:'<?php echo AJAX_URL; ?>geonames.php',
        minChars:2,
        onSelect: function(value, data) {
            $('#location_uri').val(data);
        },
    };
    a = $('#location').autocomplete(options);
});
</script>

</body>

</html>

<?php
    }

    public function users($type, $users) {
        error_log("Template::users",0);
        $ht = '<h2>'.ucfirst($type).'</h2>';
        if($users) {
            $ht .= '<ul>';
            foreach($users as $u) {
                $user = $u['uri'];
                $ht .= "<li><a href='$user'>$user</a>";
                if (SMOBAuth::check()) {
                    $t = substr($type, 0, -1);
                    $remove = REMOVE_URL."$t/$user";
                    $ht .= " [<a href=\"$remove\" onclick=\"javascript:return confirm('Are you sure ? This cannot be undone.')\">remove</a>]";
                }
                $ht .= "</li>";
            }
            $ht .= '</ul>';
        } else {
            $ht .= 'No one at the moment';
        }
        if($type == 'followings' && SMOBAuth::check()) {
            $ht .= "<p>If you want to follow new people, use the <a href=\"javascript:window.location='".FOLLOWING_ADD_URL."/'+window.location\">Follow in my SMOB!</a> bookmarklet.</p>";
        }
        return $ht;
    }

    public function person($person, $uri) {
        error_log("Template::person",0);
        $names = SMOBTools::either($person['names'], array("Anonymous"));
        $imgs = SMOBTools::either($person['images'], array(IMG_URL.'avatar-blank.jpg'));
        $homepage = $person['homepage'];
        $weblog = $person['weblog'];
        $knows = $person['knows'];

        $name = $names[0];
        $pic = $imgs[0];

        $ht = "<div class=\"person\">\n";
        $ht .= "<img src=\"$pic\" class=\"depiction\" alt=\"Depiction\" />";
        $ht .= "$name\n";

        foreach ($homepage as $h) {
            $ht .= " [<a href=\"$h\">Website</a>]\n";
        }
        foreach ($weblog as $w) {
            $ht .= " [<a href=\"$w\">Blog</a>]\n";
        }
        foreach ($knows as $k) {
            $enc = get_uri($k, 'user');
            $ht .= " [<a href=\"$enc\">Friend</a>]\n";
        }

        $ht .= "</div>\n\n";
        return $ht;
    }

}
