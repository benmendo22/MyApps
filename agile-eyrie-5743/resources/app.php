<?php
if ($_GET['pg'] == "fb") {	

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<?php 
include("../header_primary.php"); 
include("shlocations.php");
error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE | E_STRICT); // Warns on good coding standards
ini_set("display_errors", "0");

function xmlToArray($xml,$ns=null){
  $a = array();
  for($xml->rewind(); $xml->valid(); $xml->next()) {
    $key = $xml->key();
    if(!isset($a[$key])) { $a[$key] = array(); $i=0; }
    else $i = count($a[$key]);
    $simple = true;
    foreach($xml->current()->attributes() as $k=>$v) {
        $a[$key][$i][$k]=(string)$v;
        $simple = false;
    }
    if($ns) foreach($ns as $nid=>$name) {
      foreach($xml->current()->attributes($name) as $k=>$v) {
         $a[$key][$i][$nid.':'.$k]=(string)$v;
         $simple = false;
      }
    } 
    if($xml->hasChildren()) {
        if($simple) $a[$key][$i] = xmlToArray($xml->current(), $ns);
        else $a[$key][$i]['content'] = xmlToArray($xml->current(), $ns);
    } else {
        if($simple) $a[$key][$i] = strval($xml->current());
        else $a[$key][$i]['content'] = strval($xml->current());
    }
    $i++;
  }
  return $a;
}


$kml = new SimpleXMLIterator($kmlstr);
$arr = xmlToArray($kml);
$locationInfo = $arr['Document'][0]['Folder'][0]['Placemark'];


?>
<style type="text/css">
#map,#map_whp { 
  width: 100%;
  height: 100%;
  
}
</style>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAy0BntWDmwO2D7fYDEzd0meg7cRGRIC6U&sensor=true"></script>

<script type="text/javascript">

	
	
	var map;
	var infowindow = new google.maps.InfoWindow();
	var marker, i;
	var gmarkers = [];
	var mapIcon = 'https://www.salemhealth.org/images/new/icons/SH_Bug_Pin_2.png';
	var iterator = 0;
	var locations = [
		<?php
			
		foreach ($locationInfo as $place) {
			
			$shortName = $place['short'][0];
			$name = $place['name'][0];
			$description = $place['description'][0];
			$coords = $place['Point'][0]['coordinates'][0];
			$mapCoords = explode(",",$place['Point'][0]['coordinates'][0]);
			$address = $place['address'][0];
			$phone = $place['phone'][0];
			$webpage = $place['webpage'][0];
			$infoBoxContent = "\"<div class='mapContentBox'><h3>".$name."</h3><p>".$description."</p><p>".$address."<br />".$phone."</p><p><a href='".$webpage."'>".$name."</p></div>\"";
			echo "['$name','$description','$address','$phone','$webpage',$mapCoords[1],$mapCoords[0],0],\n";
		}
	?>
		[]
	];
	
	function initialize() {
		directionsDisplay = new google.maps.DirectionsRenderer();
		
		
		var mapOptions = {
			center: new google.maps.LatLng(44.906712,-123.159485),
			zoom: 12,
			zoomControlOptions: {
			  position: google.maps.ControlPosition.LEFT_BOTTOM
			},
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			
			styles: [
				{
					stylers: [ 
						{ saturation: -100 },
						{ visibility: "on" }
					]
				},
				{
				featureType: 'road.local',
					stylers: [
						  { saturation: 200 },
						  { hue: "#241D58" }
					]
				},
				{
				featureType: "administrative.neighborhood",
					elementType: "labels.text",
					stylers: [
					  { visibility: "off" }
					]
				  }
			  ]
		};

		map = new google.maps.Map(document.getElementById('map'), mapOptions);
		var bikeLayer = new google.maps.BicyclingLayer();
		//bikeLayer.setMap(map);
		
		var trafficLayer = new google.maps.TrafficLayer();
		//trafficLayer.setMap(map);
		
		//var panoramioLayer = new google.maps.panoramio.PanoramioLayer();
		//panoramioLayer.setMap(map);
		
	}
		
		
	
	// This function generates a link to a Larger Google Map defaulting to directions mode
	function directionsLink(loc) {
		var href = 'maps.google.com/maps?f=d&hl=en&saddr=&daddr=';
		// concatinate address
		href += encodeURIComponent(loc[2].replace('<br />', ','));
		// lat, long, zoom
		href += '@' + loc[5] + ',' + loc[6] + '&ie=UTF8&z=15';
		// return link
		return '<a href="http://' + href + '" target="_blank">Directions</a>';
	}
	function go() {	
		for (i = 0; i < locations.length; i++) {
			// color code marker
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(locations[i][5], locations[i][6]),
				animation: google.maps.Animation.DROP,
				map: map,
				icon: mapIcon
			});
				
			google.maps.event.addListener(marker, 'click', (function(marker, i) {
			var windowOptions = { 
				maxWidth : 350 
			}
			return function() {
				infowindow.setContent('<div class="mapContentBox"><h3>' + locations[i][0] + '</h3><p>' + locations[i][1] + '</p><p>' + directionsLink(locations[i]) + '<br />' + locations[i][2] + '<br />' + locations[i][3] + '</p><p><a href="' + locations[i][4] + '">' + locations[i][0] + '</p></div>');
			  //infowindow.setContent('<div class="infoWindow"><span class="name">' + locations[i][0] + '</span><span class="address">' + locations[i][2] + '</span><span class="directions">' + directionsLink(locations[i])+ '</span>');
			  infowindow.setOptions(windowOptions);
			  infowindow.open(map, marker);
			}
			})(marker, i));
			
			gmarkers.push(marker);
		}

		
	}//END go()


$(document).ready(function() {
	$.fancybox(
		"<h2>Salem Health's Locations<img src='https://www.salemhealth.org/images/new/icons/SH_Bug_Pin_2.png' border='0'> </h2><p>Welcome to our interactive map! To learn more about a particular office or location, click on the icon.</p>",
		{
			'autoDimensions'	: false,
			'width'         	: 300,
			'height'        	: 130,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'onClosed'		: function() {
				go();
			}
		}
		
	);
});
</script>
<body onload="initialize()">
<div id="wrapper">
	<div id="map"></div>
</div><!-- END wrapper -->
</body>
</html>
<?php
}
?>