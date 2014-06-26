<?php

/******************************************************************************
	Wallet Configuration
******************************************************************************/
	$GLOBALS["wallet_ip"] = "127.0.0.1";
	$GLOBALS["wallet_port"] = "17778";
	$GLOBALS["wallet_user"] = "admin";
	$GLOBALS["wallet_pass"] = "21123";

	// Use CURL instead of built-in functions
	// You need php-curl extension
	$GLOBALS["use_curl"] = false;
/******************************************************************************

	Block Chain And Network Information
	
	These functions return general information about 
	the block chain, the wallet itself, and the network
	the wallet/node is attached to.

******************************************************************************/

	function getblock ($block_hash)
	{
	//	The JSON-RPC request starts with a method name
		$request_array["method"] = "getblock";
	
	//	For getblock a block hash is required	
		$request_array["params"][0] = $block_hash;
	
	//	Send the request to the wallet
		$info = wallet_fetch ($request_array);
		
	//	This function returns an array containing the block 
	//	data for the specified block hash
		return ($info);
	}
	
	function getblockhash ($block_index)
	{
	//	The JSON-RPC request starts with a method name
		$request_array["method"] = "getblockhash";
	
	//	For getblockhash a block index is required	
		$request_array["params"][0] = $block_index;
	
	//	Send the request to the wallet
		$info = wallet_fetch ($request_array);
		
	//	This function returns a string containing the block 
	//	hash value for the specified block in the chain
		return ($info);
	}
	
	function getinfo () 
	{
	//	The JSON-RPC request starts with a method name
		$request_array["method"] = "getinfo";
	
	//	getinfo has no parameters
	
	//	Send the request to the wallet
		$info = wallet_fetch ($request_array);
		
	//	This function returns an array containing information
	//	about the wallet's network and block chain
		return ($info);
	}
	
	function getnetworkhashps ($block_index=NULL)
	{
	//	The JSON-RPC request starts with a method name
		$request_array["method"] = "getnetworkhashps";
	
	//	block index is an optional parameter. If no block
	//	index is specified you get the network hashrate for 
	//	the latest block
		
		if (isset ($block_index))
		{
			$request_array["params"][0] = $block_index;
		}
		
	//	Send the request to the wallet
		$info = wallet_fetch ($request_array);
		
	//	This function returns a string containing the calculated
	//	network hash rate for the latest block
		return ($info);
	}
	
	function getrawtransaction ($tx_id, $verbose=1)
	{
	//	The JSON-RPC request starts with a method name
		$request_array["method"] = "getrawtransaction";
	
	//	For getrawtransaction a txid is required	
		$request_array["params"][0] = $tx_id;
		$request_array["params"][1] = $verbose;
	
	//	Send the request to the wallet
		$info = wallet_fetch ($request_array);
		
	//	This function returns a string containing the block 
	//	hash value for the specified block in the chain
		return ($info);
	}

/******************************************************************************

	JSON-RPC Fetch
	
	This function is used to request information form the daemon.

******************************************************************************/
	
	// Uses CURL (external dependacy) to communicate with wallet
	function get_json_curl($request){
	//	Create curl connection object
		$coind = curl_init();
		
	//	Set the IP address and port for the wallet server
		curl_setopt ($coind, CURLOPT_URL, $GLOBALS["wallet_ip"]);
		curl_setopt ($coind, CURLOPT_PORT, $GLOBALS["wallet_port"]);
	
	//	Tell curl to use basic HTTP authentication
		curl_setopt($coind, CURLOPT_HTTPAUTH, CURLAUTH_BASIC) ;
	
	//	Provide the username and password for the connection
		curl_setopt($coind, CURLOPT_USERPWD, $GLOBALS["wallet_user"].":".$GLOBALS["wallet_pass"]);
	
	//	JSON-RPC Header for the wallet
		curl_setopt($coind, CURLOPT_HTTPHEADER, array ("Content-type: application/json"));
	
	//	Prepare curl for a POST request
		curl_setopt($coind, CURLOPT_POST, TRUE);
	
	//	Provide the JSON data for the request
		curl_setopt($coind, CURLOPT_POSTFIELDS, $request); 

	//	Indicate we want the response as a string
		curl_setopt($coind, CURLOPT_RETURNTRANSFER, TRUE);
	
	//	Required by RPCSSL self-signed cert
		curl_setopt($coind, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($coind, CURLOPT_SSL_VERIFYHOST, FALSE);

	//	execute the request	
		$response_data = curl_exec($coind);

	//	Close the connection
		curl_close($coind);
		
		return $response_data;
	}
	
	// Uses Built-in functions to communicate with wallet
	function get_json_fopen($request){
		$options = array(
			'http' => array(
				'method'  => 'POST',
				'content' => $request,
				'header'=>  "Content-Type: application/json\r\n" .
							"Accept: application/json\r\n"
			)
		);
		$context  = stream_context_create( $options );
		$url = 'http://' . $GLOBALS["wallet_user"].":".$GLOBALS["wallet_pass"] . '@' . $GLOBALS["wallet_ip"] . ':' . $GLOBALS["wallet_port"];
		return file_get_contents( $url, false, $context );
	}

	function wallet_fetch ($request_array)
	{
	
	//	Encode the request as JSON for the wallet
		$request = json_encode ($request_array);
		
		if($GLOBALS["use_curl"]){
			$response_data = get_json_curl($request);
		}
		else{
			$response_data = get_json_fopen($request);
		}

	//	The JSON response is read into an array
		$info = json_decode ($response_data, TRUE);
		
	//	If an error message was received the message is returned
	//	to the calling code as a string.	
		if (isset ($info["error"]) || $info["error"] != "")
		{
			return $info["error"]["message"]."(Error Code: ".$info["error"]["code"].")";
		}
		
	//	If there was no error the result is returned to the calling code
		else
		{
			return $info["result"];
		}
	}

/******************************************************************************
	This script is Copyright � 2013 Jake Paysnoe.
	I hereby release this script into the public domain.
	Jake Paysnoe Jun 26, 2013
******************************************************************************/
?>
