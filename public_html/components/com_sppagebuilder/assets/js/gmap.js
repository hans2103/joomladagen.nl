function initSPPageBuilderGMap() {
  jQuery('.sppb-addon-gmap-canvas').each(function(index) {
    var mapId = jQuery(this).attr('id'),
    zoom = Number(jQuery(this).attr('data-mapzoom')),
    mousescroll = jQuery(this).attr('data-mousescroll'),
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

    map.setMapTypeId(google.maps.MapTypeId[maptype]);

  });
};

jQuery(window).load(function() {
  initSPPageBuilderGMap();
});
