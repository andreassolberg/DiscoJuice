


var DJMap = function() {

	var centerloc = new google.maps.LatLng(60, 10);
	this.markers = [];

	var MY_MAPTYPE_ID = 'discojuice_style';

	var featureOpts = [
		{
			stylers: [
				{ hue: '#f0f055' },
				{ visibility: 'simplified' },
				{ gamma: 0.5 },
				{ weight: 0.5 }
			]
		},
		{
			elementType: 'labels',
			stylers: [
				{ visibility: 'off' }
			]
		},
		{
			featureType: 'water',
			stylers: [
				{ color: '#000033' }
			]
		}
	];

	var mapOptions = {
		zoom: 5,
		center: centerloc,
		mapTypeControlOptions: {
			mapTypeIds: [google.maps.MapTypeId.ROADMAP, MY_MAPTYPE_ID]
		},
		mapTypeId: MY_MAPTYPE_ID
	};

	this.map = new google.maps.Map(document.getElementById('map-canvas'),
		mapOptions);

	var styledMapOptions = {
		name: 'DiscoJuice Style'
	};

	var customMapType = new google.maps.StyledMapType(featureOpts, styledMapOptions);

	this.map.mapTypes.set(MY_MAPTYPE_ID, customMapType);

}

DJMap.prototype.reset = function() {
	for(var i = 0; i < this.markers.length; i++) {
		this.markers[i].setMap(null);
	}
	this.markers = [];
}

DJMap.prototype.center = function(c) {
	var nc = new google.maps.LatLng(c.lat,c.lon);
	this.map.setCenter(nc);
}

DJMap.prototype.setZoom = function(z) {
	this.map.setZoom(z);
}

DJMap.prototype.addItem = function(item) {
	if (!item.geo) return;


	var lat = null, lon = null;

	if (item.geo && item.geo.length) {
		lat = item.geo[0].lat;
		lon = item.geo[0].lon;
	} else if(item.geo.lat) {
		lat = item.lat;
		lon = item.lon;
	}
	if (!lat) return;


	var myLatlng = new google.maps.LatLng(lat, lon);
	var contentString = '<div id="content">';

	if (item.icon) {
		contentString += '<img src="https://cdn.discojuice.org/logos/' + item.icon + '" style="img-thumbnail img-responsive" />';
	}

	contentString +=
		'<div id="siteNotice">'+
		'</div>'+
		'<h4 id="firstHeading" class="firstHeading">' + item.title +'</h4>'+
		'<div id="bodyContent">'+
		'<p>EntityID <tt>' + item.entityID + '</tt></p>';


	if (item.geo) {
		contentString += '<p>Geo location ' + JSON.stringify(item.geo) + '</p>';
	}

	contentString +=
		'</div>'+
		'</div>';

	var infowindow = new google.maps.InfoWindow({
		content: contentString
		// ,maxWidth: 200
	});

	var markerOpts = {
		position: myLatlng,
		map: this.map,
		title: item.title
	};
	if (item.icon) {
		markerOpts.icon = 'https://cdn.discojuice.org/logos/' + item.icon;
	}

	var marker = new google.maps.Marker(markerOpts);
	google.maps.event.addListener(marker, 'click', function() {
		infowindow.open(this.map,marker);
	});

	this.markers.push(marker);

}