var map;

jQuery( document ).ready( function( $ ) {

	if( navigator.geolocation ) {

		navigator.geolocation.getCurrentPosition(
			getPosition,
			getPositionError
		);

	} else {
		getPositionError();
	}

	function getPosition( position ) {

		doMap(
			position.coords.latitude,
			position.coords.longitude
		);

	}

	function getPositionError() {

		doMap(
			service_map['lat'],
			service_map['lng']
		);

	}

	function doMap( lat, lng ) {

		var mapCoord = new google.maps.LatLng( lat, lng );

		var mapOptions = {
			zoom: parseInt( service_map['zoom'] ),
			center: mapCoord
		};

		map = new google.maps.Map(
				document.getElementById( 'map-canvas' ),
				mapOptions
		);

		google.maps.event.addListener(map, 'idle', function(ev){
			getSites();
		});

	}

	function getSites() {

		var bounds = map.getBounds();
		var ne = bounds.getNorthEast();
		var sw = bounds.getSouthWest();

		var corners = {
			'ne_lat': ne.lat(),
			'ne_lng': ne.lng(),
			'sw_lat': sw.lat(),
			'sw_lng': sw.lng(),
		};

		var data = {
			'action': 'get_sites',
			'bounds': corners
		};

		$.post( ajaxurl, data, function( sites ) {

			console.dir( sites ); /* list sites */

		});

	}

});

var xxx;
