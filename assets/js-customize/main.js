






var App = function(el) {
	this.el = el;

	this.feedlist = {};
	this.providerlist = [];

	this.elFeedlist = this.el.find('#feedlist');
	this.elProviderlist = this.el.find('#providerlist');

	this.elFeedlist.on('click', '.list-group-item', $.proxy(this.selectFeed, this));



	this.load();
	console.log("App loaded");
}



App.prototype.template = function(name, callback) {
	$.get('../assets/js-customize/templates/' + name + '.html', function(source) {
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

	// alert("Customize");

	var a = new App($("div#customize"));




});