<!doctype html>
<html>
<head>
	<meta charset='UTF-8' />
	<style>
    input, textarea {border:1px solid #CCC;margin:0px;padding:0px}
    </style>
	<script src="jquery.js"></script>
	<script src="fancywebsocket.js"></script>
	<script>
		var Server;

		//for diplaying message at client side only
        function log( text ) {
            $log = $('#log');
            $onlineusers = $('#onlineusers');

            if(text.indexOf(",") !== -1) {
                var strarry  = text. split(',');
                var strCount = strarry.length;
                var list     = '';
                text = strarry[(strCount-1)];
                if(text != ''){
                    $log.append(($log.val()?"\n":'')+ text);
                }

                if(strarry.length > 0) {
                    for(i=0; i<(strCount-1); i++){
                        list += '<li>'+strarry[i]+'</li>';
                    }
                    $onlineusers.empty();
                    $onlineusers.append('<ul>'+list+'</ul>');
                }
            } else {
                $log.append(($log.val()?"\n":'')+ text);
            }
            $log[0].scrollTop = $log[0].scrollHeight - $log[0].clientHeight;
        }

        //for sending data to WS server
		function send( text ) {
            Server.send( 'message', text );
        }

        //for parsing get url parameters
        function getUrlParams() {
            var params = {};
            window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str,key,value) {
                params[key] = value;
            });

            return params;
        }

		$(document).ready(function() {

            var params = getUrlParams();

            log('Connecting As: ' + params.name + '....');
            Server = new FancyWebSocket('ws://192.168.2.135:8100');

            $('#message').keypress(function(e)
            {
                if ( e.keyCode == 13 && this.value )
                {
                    log(  params.name +' Says: '+ this.value );
                    send( params.name + ':' +this.value );

                    $(this).val('');
                }
            });

            //call the user bind function to display its connected
            Server.bind('open', function() {
                log( "Connected." );
            });

            //here its diconnected
            Server.bind('close', function( data ) {
                log( "Disconnected." );
            });

            //Log messages which is sent from server
            Server.bind('message', function( payload ) {
                log( payload );
            });

            Server.connect();
        });
	</script>
</head>

<body style="max-width:800px;margin:auto">
	<div id='body' style="width: 102%">
        <div style="background: lightsalmon"><h2 style="text-align: center">Welcome to chat room </h2> </div>
        <div style="width: 100%">
        <div style="border:1px solid #CCCCCC; height: 400px; float: left; background: lightblue;">
            <i>Users Online:</i>
            <div id="onlineusers"></div>
        </div>
        <div style="float: left;">
            <textarea id='log' name='log' readonly='readonly' style="height:400px; width:425%; background: none repeat scroll 0 0 dimgrey; color: white;"></textarea><br/>
        </div>
        <div>
            <input type='text' id='message' name='message' style="width:100%;line-height:40px"/>
        </div>
        </div>

	</div>
</body>

</html>

