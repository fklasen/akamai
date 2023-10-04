<?php
set_time_limit(0);

$images= array(); //feed this array with your image host+paths
$headers="User-Agent: ".$_SERVER['HTTP_USER_AGENT']; //add headers as needed to avoid blocking by WAFs and bot protection features

//these are just examples, add any other arrays/variations as needed
$widthPolicies=array(5000,2880,720,2160,540,1440,1080); //fill this with widths needed
$policies=array("low","medium","high"); //fill this with policies, this is just an example,

foreach($images as &$imageUrl){
	foreach ($policies as &$policy) {	
		foreach ($widthPolicies as &$width) {
			try {

				$curl = curl_init();
				curl_setopt($curl, CURLOPT_NOBODY, 1); //important, avoid fetching the image content
				curl_setopt($curl, CURLOPT_URL, "https://".$imageUrl."?imwidth=".$width."&impolicy=".$policy);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_SSLVERSION, 6);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

				$body = curl_exec($curl);
				curl_close($curl);
			}
			catch (\Exception $e){
				echo "Error code: ".$e->getResponse()->getStatusCode()."<br><br>";
				echo "Error message: ".$e->getResponse()->getBody()->getContents();
			}
		}
	} 
}		
?>
