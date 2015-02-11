<?php
session_start();

// let not get server timeout
set_time_limit(0);

// including the PHPWebSocket server class
require 'class.PHPWebSocket.php';

// when a client sends data to the server
function wsOnMessage($clientID, $message, $messageLength, $binary) {

	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );


    if(isset($_SESSION[$clientID]['name']) && $_SESSION[$clientID]['name'] != '')
    {
        $_name = $_SESSION[$clientID]['name'];
        //setFileData($clientID, $_name);
        $message = $message;
    }
    else
    {
        $_message = explode(':', $message);
        $_name = $_message[0];
        //setFileData($clientID, $_name);
        $message = $_message[1];

    }

	// check if message length is 0
	if ($messageLength == 0) {
		$Server->wsClose($clientID);
		return;
	}

    $_clients = '';

    //creating avaliable users list
    foreach ( $Server->wsClients as $id => $client ) {
        $_clients .= $id .',';
    }

    /*foreach($D = getFileData() as $k => $v){
        $_clients .= $v .',';
    }*/

	//The speaker is the only person in the room. Don't let them feel lonely.
	if ( sizeof($Server->wsClients) == 1 )
		$Server->wsSend($clientID, "Nobody inside the room");
	else
		//Send the message to everyone but the person who said it
		foreach ( $Server->wsClients as $id => $client ) {
			if ( $id != $clientID ){
				//$Server->wsSend($id, "Visitor $clientID ($ip) said \"$message\"");
				$Server->wsSend($id,  $_clients. "(".$_name.") ($ip) Says:  \"$message\"");
            }
            if($id == $clientID ){
                $Server->wsSend($id, $_clients);
            }
        }
}

// when a client connects
function wsOnOpen($clientID)
{
    if(isset($_SESSION[$clientID]) && $_SESSION[$clientID] !=''){
        //unset($_SESSION[$clientID]);
    }

	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

    $_clients = '';

    //creating avaliable users list
    foreach ( $Server->wsClients as $id => $client ) {
        $_clients .= $id .',';
    }

    $Server->log( $_clients. " : $ip ($clientID) has connected." );

	//Send a join notice to everyone but the person who joined
	foreach ( $Server->wsClients as $id => $client ){
		if ( $id != $clientID ) {
            $Server->wsSend($id, $_clients . "Visitor $clientID ($ip) has joined the room.");
        }
        if($id == $clientID ){
            $Server->wsSend($id, $_clients);
        }
    }
}

// when a client closes or lost connection
function wsOnClose($clientID, $status) {
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );
    //delFileData($clientID);


    $_clients = '';

    //creating avaliable users list
    foreach ( $Server->wsClients as $id => $client ) {
        $_clients .= $id .',';
    }

    $Server->log( $_clients. ": $ip ($clientID) has disconnected." );

    //Send a user left notice to everyone in the room
    foreach ( $Server->wsClients as $id => $client )
		//$Server->wsSend($id, $_clients. "Visitor $clientID ($ip) ".$_SESSION[$clientID]['name']. " has left the room.");
		$Server->wsSend($id, $_clients. "Visitor $clientID ($ip) has left the room.");

}

function getFileData(){
    $_file = 'data.txt';
    $_fp = fopen($_file, 'r') or exit("Unable to open file!");

    $data_array = explode("\n", fread($_fp, filesize($_file)));
    $_fileDataAll = array();

    foreach($data_array as $k => $v){
        $_fileData = explode('=>',$v);
        $_fileDataAll[$_fileData[0]]= $_fileData[1];
    }
    fclose($_fp);
    return $_fileDataAll;
}

function setFileData($id, $name){
    $str = "";
    $fileData = getFileData();
    if(isset($fileData[$id])){
        $fileData[$id] = $name;
    } /*else {
        $str = "$id=>$name \n";
    }*/
    $fullstr = '';
    foreach($fileData as $k => $v){
        $fullstr .= "$k=>$v \n";
    }

    if($str != ""){
        $fullstr .= $sstr;
    }
    writeFileData($fullstr);
}

function delFileData($id){
    $fileData = getFileData();
    if(isset($fileData[$id])){
        unset($fileData[$id]);
    }
    $fullstr = '';
    foreach($fileData as $k => $v){
        $fullstr .= "$k=>$v\n";
    }
    writeFileData($fullstr);
}

function writeFileData($fileData)
{
    $_file = 'data.txt';
    $_fp = fopen($_file, 'w') or exit("Unable to open file!");
    fwrite($_fp, $fileData);
    fclose($_fp);
}

// start the server
$Server = new PHPWebSocket();
$Server->bind('message', 'wsOnMessage');
$Server->bind('open', 'wsOnOpen');
$Server->bind('close', 'wsOnClose');
$Server->wsStartServer('192.168.2.155', 8100);


?>