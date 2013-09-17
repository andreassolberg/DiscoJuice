






var App = function(el) {
	this.el = el;

	this.feedlist = {};
	this.providerlist = [];

	this.elFeedlist = this.el.find('#feedlist');
	this.elProviderlist = this.el.find('#providerlist');

	this.elFeedlist.on('click', '.list-group-item', $.proxy(this.selectFeed, this));

	this.map = new DJMap();

	this.load();
	console.log("App loaded");
}


App.prototype.selectFeed = function(e) {
	var feedid = $(e.currentTarget).data('feedid');
	var that = this;

	this.elFeedlist.find('.list-group-item').removeClass('active');
	$(e.currentTarget).addClass("active");

	console.log("Selected feed id", feedid);
	if (!this.feedlist.hasOwnProperty(feedid)) {
		// console.log(this.feedlist);
		alert("ERROR: Could not find Feed with ID " + feedid);
		return;
	}

	var f = this.feedlist[feedid];
	f.id = feedid;
	$.getJSON("https://cdn.discojuice.org/feeds/" + feedid, function(data) {

		f.feed = data;
		console.log("Feed data", f);
		that.providerlist = f;

		that.template('providerlist', function(template) {
			that.elProviderlist.empty().append(template(f));
		});
	
		that.map.reset();
		for(var i = 0; i < f.feed.length; i++) {
			that.map.addItem(f.feed[i]);
		}

		var c = that.center(f.feed);
		console.log("Center is", c);
		that.map.center(c);

	});


}

App.prototype.center = function(feed) {
	var geosum = {
		lat: {
			c: 0, v: 0.0
		},
		lon: {
			c: 0, v: 0.0
		}
	}
	for(var i = 0; i < feed.length; i++) {
		if (feed[i].geo) {
			if (isNaN(feed[i].geo.lat)) {
				console.log("NAN", feed[i].geo);
				continue;
			}
			geosum.lat.c++; geosum.lon.c++;
			geosum.lat.v += parseFloat(feed[i].geo.lat);
			geosum.lon.v += parseFloat(feed[i].geo.lon);
		}
	}
	console.log("GEOSUM", geosum);
	return {lat: (geosum.lat.v / geosum.lat.c), lon: (geosum.lon.v/geosum.lon.c)};
}

App.prototype.template = function(name, callback) {
	$.get('../assets/js-data/templates/' + name + '.html', function(source) {
		var template = Handlebars.compile(source);
		callback(template);
	})
}

App.prototype.load = function() {
	var that = this;
	$.getJSON("http://data.discojuice.org/list-feeds", function(data) {
		console.log("DATA", data);
		that.feedlist = data;

		that.template('feedlist', function(template) {
			that.elFeedlist.empty().append(template(data));
		});

	});


}




$(document).ready(function() {

	var a = new App($("div#djdata"));




});