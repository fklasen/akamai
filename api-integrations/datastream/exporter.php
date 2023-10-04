<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
include 'akamai-open-edgegrid-client-0.4.6.phar';
include '../include/credentials';
header('Content-Type: text/plain; version=0.0.4; charset=utf-8');

//database connection
$link =  mysqli_connect($DBServer, $DBUser, $DBPass, $DBName);
 
if (!$link){
    die ('Connect error (' . mysqli_connect_errno() . '): ' . mysqli_connect_error() . "\n");
} 
$link->set_charset("utf8");	

$sqllist="SELECT * FROM `streams` ORDER BY id ASC";

$streams=mysqli_query($link, $sqllist)
or die("db-error");

//order of stream for metric appended to metric name
$x=1;

while($row = mysqli_fetch_array($streams)){

	$streamid=$row['streamid'];
	$base_url=$row['host'];

	/// API client initialization

	$client = new Akamai\Open\EdgeGrid\Client(['base_uri' => "https://".$base_url]);

	$client->setAuth($client_token, $client_secret, $access_token);

	/////////////////////////////

	///API call initilization
    $timeZone = new DateTimeZone("UTC");
    $timeTo = new DateTime();
    $timeTo->setTimeZone($timeZone);
    $timeTo->modify("-60 seconds"); // 20 minutes time range to be certain the latest is in the response
    $timeTo=rawurlencode($timeTo->format("Y-m-d\TH:i:s\Z"));

    $timeFrom = new DateTime();
    $timeFrom->setTimeZone($timeZone);
    $timeFrom->modify("-1200 seconds"); // 20 minutes time range to be certain the latest is in the response
    $timeFrom=rawurlencode($timeFrom->format("Y-m-d\TH:i:s\Z"));

	$path="/datastream-pull-api/v1/streams/".$streamid."/aggregate-logs?start=$timeFrom&end=$timeTo&page=0&size=10";

	/////////////////////////////


	//run the API-call

	try{

		$response = $client->request('GET',$path);
		$response = json_decode($response->getBody());

		$orderedResponse = array(); //used to reorder the responses as the time ranges returned are scrambled each time

		foreach($response->data as $data){
			$orderedResponse[]=$data->startTime;
		}

		rsort($orderedResponse); //reordering

		//verify which data is the most recent, echo that data for prometheus pulling
		foreach($response->data as $data){
			if($data->startTime==$orderedResponse[0]){
				$total=(int)$data->{'2xx'}+(int)$data->{'3xx'}+(int)$data->{'4xx'}+(int)$data->{'5xx'};
				$edgeResponseTime=str_replace("ms", "", $data->{'edgeResponseTime'});
				$originResponseTime=str_replace("ms", "", $data->{'originResponseTime'});
				echo "number_of_req_".$x." ".$total."\n";
				echo "number_of_2xx_".$x." ".(int)$data->{'2xx'}."\n";			
				echo "number_of_3xx_".$x." ".(int)$data->{'3xx'}."\n";
				echo "number_of_4xx_".$x." ".(int)$data->{'4xx'}."\n";
				echo "number_of_5xx_".$x." ".(int)$data->{'5xx'}."\n";
				echo "requests_per_second_".$x." ".$data->{'requestsPerSecond'}."\n";
				echo "bytes_per_second_".$x." ".(int)$data->{'bytesPerSecond'}."\n";
				echo "edgeResponseTime_".$x." ".$edgeResponseTime."\n";
				echo "originResponseTime_".$x." ".$originResponseTime."\n";
				echo "numCacheHit_".$x." ".(int)$data->{'numCacheHit'}."\n";
				echo "numCacheMiss_".$x." ".(int)$data->{'numCacheMiss'}."\n";
        echo "offloadRate_".$x." ".(int)$data->{'offloadRate'}."\n";

			}
		}
	}

	//error handling
	catch (\Exception $e) {
		echo "Error code: ".$e->getResponse()->getStatusCode()."<br><br>";
		echo "Error message: ".$e->getResponse()->getBody()->getContents();
	}
	
	$x++; //increment stream #
}

mysqli_close($link)

?>
