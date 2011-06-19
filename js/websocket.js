function log(message) {
	var state = document.createElement('div');
	state.innerHTML = message;
	document.getElementById('main').appendChild(state);
}

function openConnection() {
    if ( !conn.readyState || conn.readyState > 1 ) {

        conn = new WebSocket( serverUri );

        conn.onopen = function () {
            //state.innerHTML = "Socket opened";
            //state.className = "open";
            console.debug("Socket opened");
        };

        conn.onmessage = function( event ) {
            var string = event.data;
            //var code = format_xml(string).replace(/></,'').replace(/\&/g,'&'+'amp;').replace(/</g,'&'+'lt;').replace(/>/g,'&'+'gt;').replace(/\'/g,'&'+'apos;').replace(/\"/g,'&'+'quot;')
            //$('#messages').prepend("<pre class='sh_xml'><code>"+ code + "</code></pre>");
            //sh_highlightDocument(); 
            //if($('#messages').children().size() > 5) {
            //    $('#messages pre:last-child').remove();
            //}
            console.debug(string);
        };

        conn.onclose = function( event ) {
            //state.innerHTML = "x";
            //state.className = "closed";
            console.debug("socket closed");
        };
    }
}    
    
          
