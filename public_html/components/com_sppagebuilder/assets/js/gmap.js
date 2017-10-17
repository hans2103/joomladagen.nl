function initSPPageBuilderGMap() {
  jQuery('.sppb-addon-gmap-canvas').each(function(index) {
    var mapId = jQuery(this).attr('id'),
    zoom = Number(jQuery(this).attr('data-mapzoom')),
    infowindow = jQuery(this).attr('data-infowindow'),
    mousescroll = (jQuery(this).attr('data-mousescroll') === 'true') ? true : false,
    maptype = jQuery(this).attr('data-maptype'),
    latlng = {lat: Number(jQuery(this).attr('data-lat')), lng: Number(jQuery(this).attr('data-lng'))};

    var map = new google.maps.Map(document.getElementById(mapId), {
      center: latlng,
      zoom: zoom,
      scrollwheel: mousescroll
    });

    var marker = new google.maps.Marker({
      position: latlng,
      map: map
    });

    //has infowindow text
    if (infowindow) {
      var infowindow = new google.maps.InfoWindow({
        content: atob(infowindow)
      });
      marker.addListener('click', function() {
        infowindow.open(map, marker);
      });
    }

    map.setMapTypeId(google.maps.MapTypeId[maptype]);

  });
};

jQuery(window).load(function() {
  initSPPageBuilderGMap();
});
