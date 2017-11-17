<?php

defined('_JEXEC') or die;

$apikey    = 'AIzaSyCKtJ-8aCn4iZmE_VigVYGQJSS9CpX3qmU';
$latitude  = $displayData['latitude'];
$longitude = $displayData['longitude'];
$title     = PWTTemplateHelper::getSitename();

// Markers for the map
$markers = array();

array_push($markers, json_encode(array(
	'title'   => 'JoomlaDagen @ High Tech Campus Eindhoven',
	'lat'     => $displayData['latitude'],
	'lng'     => $displayData['longitude'],
	'url'     => 'https://www.google.nl/maps/place/Conference+Center+High+Tech+Campus' . '/@' . $displayData['latitude'] . ',' . $displayData['longitude'] . ',17z',
	'address' => $displayData['adres'] . " ," . $displayData['postcode'] . " " . $displayData['woonplaats'],
	'icon'    => JUri::root() . 'images/joomla_marker.png'
)));
?>
<div class="block__gmap--wrapper">
    <div id="map-canvas" class="block__gmap--canvas"></div>
</div>
<script type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apikey; ?>"></script>
<script type="text/javascript">
    function initialize(offset) {
        var myLatlng = new google.maps.LatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>);
        var mapOptions = {
            center: myLatlng,
            zoom: 13,
            zoomControlOptions: {style: google.maps.ZoomControlStyle.SMALL},
            mapTypeControl: false,
            streetViewControl: false,
            scrollwheel: false,
            keyboardShortcuts: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            styles: [
                {
                    stylers: [
//                        {hue: "<?php //echo $color; ?>//"},
//                        {saturation: -20}
                    ]
                }, {
                    featureType: "road",
                    elementType: "geometry",
                    stylers: [
                        {lightness: 100},
                        {visibility: "simplified"}
                    ]
                }, {
                    featureType: "road",
                    elementType: "labels",
                    stylers: [
                        {visibility: "simplified"}
                    ]
                }, {
                    featureType: "poi",
                    elementType: "labels",
                    stylers: [
                        {visibility: "off"}
                    ]
                }, {
                    featureType: "poi.business",
                    elementType: "labels",
                    stylers: [
                        {visibility: "off"}
                    ]
                }, {
                    featureType: "water",
                    elementType: "labels",
                    stylers: [
                        {visibility: "off"}
                    ]
                }
            ]
        };
        var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
        //var image = new google.maps.MarkerImage('<?php //echo $marker; ?>', new google.maps.Size(50, 50), new google.maps.Point(0, 0), new google.maps.Point(15, 50), new google.maps.Size(50, 50));
        var center;
        var markers = [];
        var bounds = new google.maps.LatLngBounds();
        var infoWindow = new google.maps.InfoWindow();
        var locations = [ <?php echo implode(',', $markers) ?> ];

        for (i = 0; i < locations.length; i++) {
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i].lat, locations[i].lng),
                map: map,
                icon: new google.maps.MarkerImage(locations[i].icon, new google.maps.Size(50, 50), new google.maps.Point(0, 0), new google.maps.Point(15, 50), new google.maps.Size(50, 50)),
                url: locations[i].url,
            });

            google.maps.event.addListener(marker, 'click', (function (marker, i, infoWindow) {
                return function () {
                    infoWindow.setContent('<a href="' + locations[i].url + '" target="_blank">' + locations[i].title + '</a><br />' + locations[i].address + '<br /><a href="' + locations[i].url + '" target="_blank">routebeschrijving</a>');
                    infoWindow.open(map, marker);
                }
            })(marker, i, infoWindow));

            markers.push(marker);
            bounds.extend(marker.position);
        }


        function calculateCenter() {
            center = map.getCenter();
        }

        google.maps.event.addDomListener(map, "idle", function () {
            calculateCenter();
        });
        google.maps.event.addDomListener(window, "resize", function () {
            map.setCenter(center);
        });
    }

    initialize();
    google.maps.event.addDomListener(window, "resize", function () {
        initialize();
    });

</script>