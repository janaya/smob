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
  <link rel="icon" type="image/png" href="<?php echo $root; ?>img/smob-icon.png" />
  <link rel="stylesheet" type="text/css" href="<?php echo $root; ?>css/style.css" />


  <script type="text/javascript" src="<?php echo $root; ?>js/jquery.js"></script>
  <script type="text/javascript" src="<?php echo $root; ?>js/jquery-ui.min.js"></script>
  <script type="text/javascript" src="<?php echo $root; ?>css/jquery.ui.all.css"></script>
  
  <script type="text/javascript" src="<?php echo $root; ?>js/jquery.timers-1.2.js"></script>
  <script type="text/javascript" src="<?php echo $root; ?>js/jquery.autocomplete-min.js"></script>
  <script type="text/javascript" src="<?php echo $root; ?>js/jquery.rating.js"></script>

  
  <script type="text/javascript" src="<?php echo $root; ?>js/smob.js"></script>

  <base href="<?php echo $root; ?>" />
  <script type="text/javascript">
    var state = 0;
    var maxstate = 6;
    $(function() {
        $("#step").click(function () {
            process();
        });
    });
  </script>



<body about="<?php echo SMOB_ROOT; ?>" typeof="smob:Hub sioct:Microblog">

<div id="full">

<div id="header">
<h1><a href="<?php echo SMOB_ROOT; ?>">SMOB</a></h1>
<h2><span class="smob">S</span>emantic-<span class="smob">M</span>icr<span class="smob">OB</span>logging</h2>
</div>

<div id="main">

<div class="left">

    <div id="head">
        <h2>1. Server setup</h2>
        <p>
            Welcome to the installer of your SMOB hub. 
            Before starting, you must ensure that you have met the following requirements:
        </p>
        <ul>
            <li>Download <a href="https://github.com/semsol/arc2/archives/master">ARC2</a> and unzip it in the current <code>lib</code> folder and rename it from to <code>arc</code>;</li>
            <li>Download <a href="http://sourceforge.net/projects/phpxmlrpc/files/phpxmlrpc/">XML-RPC for PHP</a>, unzip it in the current <code>lib</code> folder and rename it from <code>xmlrpc-version</code> to <code>xmlrpc</code>;</li>
            <li>Make the <code>config</code> directory writable by your web server;
            <li>If your SMOB hub is not in the <code>/smob</code> directory of your website, please edit the <code>.htaccess</code> file accordingly;</li>
            <li>Edit the <code>auth/.htaccess</code> file for authentication purposes. If you use <code>htpasswd</code> authentication, do not forget to create this file. You can use the <a href="http://www.htaccesstools.com/htpasswd-generator/">htpasswd generator here</a>.</li>
        </ul>
    </div>
    <div id="smob-db-pane">
        <h2>2. Database setup of SMOB</h2>
        <div id="smob-db-pane-in">
            <form>
                <fieldset>
                    <legend>MySQL database settings</legend>
                    <label for="db-host">database host:</label> <input type="text" id="db-host" name="db-host" value="localhost" size="50"><br />
                    <label for="db-name">database name:</label> <input type="text" id="db-name" name="db-name" value="smob" size="20"><br />
                    <label for="db-store">RDF store name:</label> <input type="text" id="db-store" name="db-store" value="smob" size="20"><br />
                    <label for="db-user">user name:</label> <input type="text" id="db-user" name="db-user" value="root" size="10"><br />
                    <label for="db-pwd">password:</label> <input type="password" id="db-pwd" name="db-pwd" value="root" size="10"><br />
                </fieldset>
            </form>
            <p class="note">
            If the database does not exist yet, it will be created it for you.
            </p>
        </div>
        <div id="smob-db-pane-out">
            <em>Request sent...</em>
        </div>
    </div>
    <div id="smob-settings-pane">
        <h2>3. SMOB settings</h2>
        <div id="smob-settings-pane-in">
            <form>
                <fieldset>
                    <legend>SMOB settings</legend>
                    <label for="smob-root">SMOB hub address:</label> <input type="text" id="smob-root" name="smob-root" value="<?=$params['root'];?>" size="50"><br />
                    <label for="smob-purge">Purge posts after <input type="text" id="smob-purge" name="smob-purge" value="0" size="2"> days (0 to keep them)</label> <br />
                    <label for="smob-hub-publish">SMOB PubSubHubbub hub address:</label> <input type="text" id="smob-hub" name="smob-hub" value="<?=$params['smob_hub'];?>" size="30"><br />
                    <label for="smob-websocket-host">SMOB websocket host:</label> <input type="text" id="smob-websocket-host" name="smob-websocket-host" value="<?=$params['smob_websocket_host'];?>" size="10"><br />
                    <label for="smob-websocket-port">SMOB websocket port:</label> <input type="text" id="smob-websocket-port" name="smob-websocket-port" value="<?=$params['smob_websocket_port'];?>" size="6"><br />
                </fieldset>
            </form>
        </div>
        <div id="smob-settings-pane-out">
            <em>Request sent...</em>
        </div>
    </div> 
    <div id="smob-user-pane">
        <h2>4. User settings</h2>
        <div id="smob-user-pane-in">
            <form>
                <fieldset>
                    <legend>FOAF settings</legend>
                    <p class="note">
                    Using your existing FOAF URI will provide distributed user-profile, and will be used to link to sign your posts. 
                    (It will also be used as well to authenticate via FOAF-SSL if you wish to do so.).
                    If you do not have a FOAF profile, you can create one <a href="http://foafbuilder.qdos.com/">here</a> or <a href="http://www.ldodds.com/foaf/foaf-a-matic">there</a>, or use your Twitter account via <a href="http://semantictweet.com">SemanticTweet</a>.
                    </p>
                    <p class="note">
                    <b>Please also note that this is your personal URI and not the URL of your FOAF profile. For more information about the difference between both, you can check the <a href="http://pedantic-web.org/fops.html#inconsist">Pedantic Web page</a> on the topic. In addition, that URI must be dereferencable and must return RDF information about itself.</b
                    </p>
                    <label for="smob-uri">FOAF URI:</label> <input type="text" id="smob-uri" name="smob-uri" value="" size="50"><br />
                    <p class="note">
                    If you do not want to create a FOAF profile, you can simply fill-in the following details and SMOB will create one for you.
                    </p>
                    <label for="smob-username">Name:</label> <input type="text" id="smob-username" name="smob-username" value="" size="50"><br />
                    <label for="smob-depiction">Picture:</label> <input type="text" id="smob-depiction" name="smob-depiction" value="" size="50"><br />
                </fieldset>
                <fieldset>
                    <legend>Authentication method</legend>
                    <input type="radio" name="smob-auth" id="smob-auth" value="htpasswd"> htpasswd (default)<br/>
                    <input type="radio" name="smob-auth" id="smob-auth" value="foafssl" checked="true"> foafssl<br/>
                </fieldset>
                <fieldset>
                    <legend>Twitter integration</legend>
                    <input type="checkbox" id="smob-twitter-read" name="smob-twitter-read"> Integrate my Twitter messages in SMOB<br />
                    <input type="checkbox" id="smob-twitter-post" name="smob-twitter-post"> Publish my SMOB updated to Twitter<br />                    
                    <label for="smob-twitter-login">Twitter login:</label> <input type="text" id="smob-twitter-login" name="smob-twitter-login" value="" size="50"><br />
                    <label for="smob-twitter-pass">Twitter pass:</label> <input type="password" id="smob-twitter-pass" name="smob-twitter-pass" value="" size="50"><br />
                    <p class="note">
                    Twitter login / password is optional.
                    </p>
                </fieldset>    
            </form>
        </div>
        <div id="smob-user-pane-out">
            <em>Request sent...</em>
        </div>
    </div>
    <div id="done-pane">
    </div>
    <button id="step">Ready ? Go !</button>


</div>
</div>

</body>

</html>
