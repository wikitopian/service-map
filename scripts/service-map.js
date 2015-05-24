function service_map_init() {

	var mapOptions = {
		zoom: parseInt( service_map['zoom'] ),
		center: new google.maps.LatLng( service_map['lat'], service_map['lng'] )
	};

	var map = new google.maps.Map(
		document.getElementById( 'map-canvas' ),
		mapOptions
	);
}

jQuery( document ).ready(function() {
	service_map_init();
});
