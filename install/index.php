<?php

require_once(dirname(__FILE__).'/../lib/smob/client.php'); 

function get_core() {
	
$core = <<<_END_
<div id="head">
	<p>
		Welcome to the <a href="http://smob.sioc-project.org/">SMOB</a> Installer. In the following we will together set up SMOB (both Client and Server).
	</p>
	<button id="step">START!</button>
</div>
<div id="main">
	<div id="get-files-pane">
		<h2>1. Install SMOB dependencies (i.e. ARC2 library)</h2>
		<div id="get-files-pane-in">
			<form>
				<fieldset>
					<legend>Download dependencies</legend>
					<label for="path-wget"><code>wget</code> path:</label> <input type="text" id="path-wget" name="path-curl" value="/usr/bin/" size="50"><br />
					<label for="path-tar"><code>tar</code> path:</label> <input type="text" id="path-tar" name="path-tar" value="/usr/bin/" size="10"><br />
					<label for="path-curl"><code>curl</code> path:</label> <input type="text" id="path-curl" name="path-curl" value="/usr/bin/" size="10"><br />
				</fieldset>
			</form>
			<p class="note">
			The ARC2 library will be automatically downloaded and extracted from the <a href="http://arc.semsol.org">project website</a>. 
			</p>
		</div>
		<div id="get-files-pane-out">
			<em>Request sent...</em>
		</div>
	</div>
	<div id="create-db-pane">
		<h2>2. Database setup of SMOB</h2>
		<div id="create-db-pane-in">
			<form>
				<fieldset>
					<legend>MySQL database settings</legend>
					<label for="db-host">database host:</label> <input type="text" id="db-host" name="db-host" value="localhost" size="50"><br />
					<label for="db-name">database name:</label> <input type="text" id="db-name" name="db-name" value="smob" size="20"><br />
					<label for="db-user">user name:</label> <input type="text" id="db-user" name="db-user" value="root" size="10"><br />
					<label for="db-pwd">password:</label> <input type="text" id="db-pwd" name="db-pwd" value="root" size="10"><br />
				</fieldset>
			</form>
			<p class="note">
			Note that these are the default values. I'm guessing you're running the database on <em>localhost</em>. If this is not the case, you need to tell me where the database is available (e.g. <em>server:8889</em>). It might be a good idea to create a dedicated SMOB user later on with phpMyadmin or the like, especially if you're using this system on the Web. If the database does not exist yet, I'm gonna create it for you.
			</p>
		</div>
		<div id="create-db-pane-out">
			<em>Request sent...</em>
		</div>
	</div>
	<div id="smob-config-pane">
		<h2>2. SMOB config</h2>
		<div id="smob-config-pane-in">
			<form>
				<fieldset>
					<legend>SMOB Server settings</legend>
					<label for="server-key">server key:</label> <input type="text" id="server-key" name="server-key" value="" size="50"><br />
					<label for="server-gmap">GoogleMap API key:</label> <input type="text" id="server-gmap" name="server-gmap" value="" size="50"><br />
					
				</fieldset>
				<fieldset>
					<legend>SMOB Client settings</legend>
					<label for="client-ping">SMOB servers:</label> <textarea id="client-ping" name="client-ping" value="" rows="5" cols="50">'http://smob.sioc-project.org/server' => ''</textarea><br />
					
					<label for="client-uri">FOAF URI:</label> <input type="text" id="client-uri" name="client-uri" value="" size="50"><br />
					<label for="client-nick">Nickname:</label> <input type="text" id="client-nick" name="client-nick" value="" size="50"><br />
					
					<label for="client-twitter-login">Twitter login:</label> <input type="text" id="client-twitter-login" name="client-twitter-login" value="" size="50"><br />
					<label for="client-twitter-pass">Twitter pass:</label> <input type="text" id="client-twitter-pass" name="client-twitter-pass" value="" size="50"><br />
					
				</fieldset>	
			</form>
			<p class="note">
			You are not required to install both the server and the client, that is up to you ! The server key will allow you to restrict updates to people that have it. Let this field empty if you do not want to set-up one. @@TODO
			</p>
		</div>
		<div id="smob-config-pane-out">
			<em>Request sent...</em>
		</div>
	</div> 
	<div id="done-pane">
	<p>Congrats! You have successfully set up SMOB and can 
                <a href="..">use it now</a>.</p>
	</div>
</div>
_END_;

return $core;

}

smob_go(get_core());

?>