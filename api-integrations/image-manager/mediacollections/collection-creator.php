<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

include 'akamai-open-edgegrid-client-0.4.6.phar';
include '../include/credentials';

/*table schema dump, note that the items collection has been is prepopulated elsewhere during image upload. 
Change to whatever needed to feed the collection with proper image URL's.

CREATE TABLE `collections` (
  `id` int(11) NOT NULL,
  `extid` varchar(255) NOT NULL,
  `published` int(2) NOT NULL,
  `front` varchar(255) NOT NULL
); 

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `number` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL
);
*/

//this stores the collection information
$link =  mysqli_connect($DBServer, $DBUser, $DBPass, $DBName);
 
if (!$link){
  die ('Connect error (' . mysqli_connect_errno() . '): ' . mysqli_connect_error() . "\n");
} 
$link->set_charset("utf8");	

//this stores the item information
$link2 =  mysqli_connect($DBServer, $DBUser, $DBPass, $DBName2);
 
if (!$link2){
  die ('Connect error (' . mysqli_connect_errno() . '): ' . mysqli_connect_error() . "\n");
} 
$link2->set_charset("utf8");	


///////////////////////////////////////
//////////publish unpublish collections
///////////////////////////////////////
if(isset($_GET['publish'])){
	$extid=$_GET['publish'];
	$sql="UPDATE `collections` SET `published` = '1' WHERE `collections`.`extid` = '$extid';";

	$result = mysqli_query($link, $sql)
			or die("Publish broke");
}
elseif(isset($_GET['unpublish'])){

	$extid=$_GET['unpublish'];
	$sql="UPDATE `collections` SET `published` = '0' WHERE `collections`.`extid` = '$extid';";
	
	$result = mysqli_query($link, $sql)
			or die("Unpublish broke");


}

///////////////////////////
///////////list images
///////////////////////////

function listimages(){
	global $link2;
	
	if(!isset($_GET['showall'])){
		$sql="SELECT * FROM `items` ORDER BY `items`.`id` DESC LIMIT 50";
	}
	else{
		$sql="SELECT * FROM `items` ORDER BY `items`.`id` DESC";
	}
	
	$result = mysqli_query($link2, $sql)
	or die("SQL error #0");
	
    //make use of image manager to transform larger images to thumbnails
	while($row = mysqli_fetch_array($result)){
		$collectionURL="https://path.to.image".$row['number'];
		if(strpos($collectionURL, ".mov")==true ){
			$imgsrc=str_replace(".mov", ".jpg", $row['number']);
			echo '<option data-img-src="img/processed/'.$imgsrc.'?impolicy=admin_thumb_watermark" value="'.$collectionURL.'"></option>';
		}
		else{
			echo '<option data-img-src="img/processed/'.$row['number'].'?impolicy=admin_thumb" value="'.$collectionURL.'"></option>';
		}
	}
}




/////////////////////////////
////////add new collection
/////////////////////////////
if(isset($_POST['urls'])){
		
	//set unique ID for the collection
	$id=bin2hex(random_bytes(25));

    /// API client initialization

    $client = new Akamai\Open\EdgeGrid\Client(['base_uri' => "https://".$base_url]);

    $client->setAuth($client_token, $client_secret, $access_token);
	
    //pick up the urls selected
    $urls=$_POST['urls'];
    
    //reverse the array to get the front image first
    $urls=array_reverse($urls);
    
    $array=array();
    
    $imgcount=0;
    $videocount=0;
    //create the json object for the API call
    foreach($urls as $url){
        if(strpos($url, ".jpg") !== false){
            $array[] = array('type' => "image", "url"=> $url);
            $front=str_replace("https://path.to.image", "", $url);

            $imgcount++;
        }
        else{
				$thumbsrc=str_replace(".mov", ".jpg", $url);
				$array[] = array('type' => "video", "url"=> $url, "mime" => "video/mp4", "poster" => $thumbsrc);
				$videocount++;
			}
		}
		sort($array);
		$items=["items" => $array];
		$obj = (object) [
	    'id' => $id,
	    'description' => "N/A",
	    'definition' => $items
		];
		
		//create the collection	
		if($imgcount==1 && $videocount==1){
		
			try {
		
				$response = $client->request('PUT', '/imaging/v0/imagecollections', ['json' => $obj]);
	
				$insert="INSERT INTO `collections` (`id`, `extid`, `front`) VALUES (NULL, '$id', '$front');";
	
				$result = mysqli_query($link, $insert)
				or die("sql error 1");
	    
			} 
			catch (RequestException $e) {

			    // Catch all 4XX errors 
				    if ($e->getResponse()->getStatusCode() == '400') {
					echo $e->getResponse()->getBody()->getContents();
			    }
			} 
			catch (\Exception $e) {

	 	    // There was another exception.
	
			}
		}
		else{
			
			   echo "<div class='blink_me' style='padding-left:-15px;color:red;'>You have to select exactly 1 image and 1 video!</div>";

		}
		
}
?>

<html>
    <body>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>IM Media Collections</title>
            <link rel="stylesheet" type="text/css" href="css/image-picker.css?v=1">
            <link rel="stylesheet" type="text/css" href="akamai-viewer/css/akamai-viewer.css?v=1">
            <script src="js/jquery-3.2.1.min.js" type="text/javascript"></script>
            <script src="js/image-picker.min.js" type="text/javascript"></script>
            <script src="akamai-viewer/js/akamai-viewer.js?v=1" type="text/javascript"></script>
            <style>
            .button{
                padding:2px;
                color:white;
                text-decoration:none;
                border-style: solid;
                border-width: 1px;
                border-color: rgb(255, 255, 255);
                border-radius: 3px;
                background-image: -moz-linear-gradient( 90deg, rgb(231,125,20) 0%, rgb(255,153,51) 100%);
                background-image: -webkit-linear-gradient( 90deg, rgb(231,125,20) 0%, rgb(255,153,51) 100%);
                background-image: -ms-linear-gradient( 90deg, rgb(231,125,20) 0%, rgb(255,153,51) 100%);
                
            }
            h1{

                margin-bottom:0px;
                margin-top:10px;
                font-family:Ubuntu,Ubuntu,Helvetica,Arial,Lucida Grande,sans-serif;
                font-weight: 300;
                line-height: 2.6rem;
                font-size: 2rem;
                -webkit-transition: color .1s,background-color .1s;
                transition: color .1s,background-color .1s;
                color: #09c;
                text-decoration: none;
            }
            body{
                background-color:black;
                font-family:Ubuntu,Ubuntu,Helvetica,Arial,Lucida Grande,sans-serif;
                color: #ff9933;
            }

            a {
                font-family:Ubuntu,Ubuntu,Helvetica,Arial,Lucida Grande,sans-serif;
                color: #ff9933;
            }

            #left {
                float:left;
                width:50%;
            }

            #right {
                float:left;
                width:50%;
            }
            .blink_me {
            animation: blinker 1s linear infinite;
            }

            @keyframes blinker {
            50% {
                opacity: 0;
            }
            }
            </style>


            <script type="text/javascript">
            var $ = window.jQuery;
            $(document).ready(function(){
                $("[data-akamai-viewer]").each(function(){
                var viewer = new Akamai.Viewer($(this), {
                    
                        
                    items: {
                    uri: "akamai-viewer/product.imviewer?imcollection=" + $(this).attr("data-akamai-viewer")
                    },
                    
                    magnifier: {
                        enabled: false,
                        hoverZoomWithoutClick: false
                    }
                    
                });
                });
            });
            </script>

        </head>
        <div id='left'>
<?php
echo "<h1>Create new collection </h1>Select one image and one video to include in the collection and click on Create collection.<br><br>";

if(!isset($_GET['showall'])){
	echo '<a href="collection-creator.php?showall=true">Show all objects</a><br><br>';
}
else{
	echo '<a href="collection-creator.php">Only show last 10 objects</a><br><br>';
}

?>
            <form method='POST' action='#'>
                <input type="submit" value="Create collection"><br><br>

                <div class="picker">
                  <select multiple="multiple" class="image-picker show-html" name="urls[]">

<?php
listimages();
?>

                    </select>
                </div>
            </form>
        </div>

        <div id='right'>
            <h1>Publish collection</h1>
            Click the button aligned to the collection to publish it on the big screen. Click again to remove it.<br><br>
<?php

if(!isset($_GET['showallcollections'])){
	echo '<a href="collection-creator.php?showallcollections=true">Show all collections</a><br><br>';
	$sql="SELECT id,extid,published,front FROM `collections` ORDER BY id DESC LIMIT 10";
}
else{
	echo '<a href="collection-creator.php">Show latest collection</a><br><br>';
	$sql="SELECT id,extid,published,front FROM `collections` ORDER BY id DESC";
}

echo "<br><br>";

$result = mysqli_query($link, $sql)
	or die("sql error 2");

while($row = mysqli_fetch_array($result)){
	echo "<div style='display:inline-block;padding:5px;'><div data-akamai-viewer='".$row['extid']."' style='width:300px;height:200px;' data-akamai-carousel-thumbnail-placement='right'></div>";
	if($row['published']==0){	
		echo "<a href='collection-creator.php?publish=".$row['extid']."' class='button' style='top:-24px;left:4px;position:relative;'>Publish</a>";
	}
	else{
		echo "<a href='collection-creator.php?unpublish=".$row['extid']."' class='button' style='top:-24px;left:4px;position:relative;'>Unpublish</a>";
	}
	echo "</div>";
}

mysqli_close($link)
or die("close error");		


?>
        </div>
        <script type="text/javascript">
            $("select").imagepicker()
        </script>
    </body>
</html>
