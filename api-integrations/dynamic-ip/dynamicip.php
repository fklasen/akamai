<?php
	
  //curl to get the new ip
  $url = "http://whatismyip.akamai.com/";
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL,$url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // Return page in string
  curl_setopt($curl, CURLOPT_ENCODING , "gzip");     
  curl_setopt($curl, CURLOPT_TIMEOUT, 60);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); // Follow redirects

  $ip = curl_exec($curl);

  if (curl_errno($curl)) {
      print "Error: " . curl_error($curl);
      die();
  } else {
      curl_close($curl);
  } 

	include 'akamai-open-edgegrid-client-0.4.6.phar';

	//update the following parameters, general recommendation is to not keep secrets in this file
	$client_token="myclienttoken";
	$client_secret="myclientsecret";
	$access_token="myaccesstoken";

	$client = new Akamai\Open\EdgeGrid\Client([
		'base_uri' => 'https://my.url.to.open.api'
	]);

	$zone="example.com"; //the zone you need to update your origin record in
	$origin="origin"; //the actual origin record to search for
	//end update parameters


	$client->setAuth($client_token, $client_secret, $access_token);
		
	try{

        $response = $client->request('GET', '/config-dns/v1/zones/'.$zone);
        $response = json_decode($response->getBody());
        $newToken = $response->token; //get the new token
        $exSerial = $response->zone->soa->serial; //get the existing serial
        $newSerial = $exSerial+1; //increase the existing serial with 1
        $newZone = $response; //create the new zone
        
        //find the origin in the array and set that key value to the new IP if found
	$index = array_search($origin, array_column($response->zone->a, 'name'));
	if ($index !== false) {
    	     $newZone->zone->a[$index]->target = $ip;
             $newZone->zone->soa->serial=$newSerial; //set the new SOA serial in the new zone
             var_dump(json_encode($newZone)); //output the whole new zone for troubleshooting
             $response = $client->request('POST', '/config-dns/v1/zones/'.$zone, ['json' => $newZone]);
	}
	else{
	     echo "Origin not found: <br><br>";
             echo $response->getBody(); //output the result for troubleshooting
	}
  }
	catch (\Exception $e) {
		echo "Error code: ".$e->getResponse()->getStatusCode()."<br><br>";
		echo "Error message: ".$e->getResponse()->getBody()->getContents();
	}
?>
