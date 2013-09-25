






var App = function(el) {
	this.el = el;

	this.type = 'embed';

	this.feedlist = {};
	this.providerlist = [];

	this.elFeedlist = this.el.find('#feedlist');
	this.elProviderlist = this.el.find('#providerlist');

	// this.elFeedlist.on('click', '.list-group-item', $.proxy(this.selectFeed, this));


	// $('form#customizeform input[name=integrationType]').change($.proxy(this.update, this));
	// $('form#customizeform input').change();

	$('form#customizeform').on('change', 'input', $.proxy(this.update, this));

	this.load();
	console.log("App loaded");


}


App.prototype.update = function(e) {
	if (e) {
		e.preventDefault();
		e.stopPropagation();

	}

	// console.log("SELECTED", $(e.currentTarget).val() );
	this.type = $("form#customizeform input[name=integrationType]:checked").val();

	var enable = {}, enabled = {};

	if (this.type === 'embed') {

		enable.title = true;
		enable.software = true;
		enable.addfeeds = true;
		enable.disco = true;
		enable.redirect = true;
		

	} else if (this.type === 'sp') {

		enable.title = true;
		enable.software = true;
		enable.addfeeds = true;
		enable.disco = true;
		enable.redirect = false;


	} else if (this.type === 'fed') {

		enable.title = false;
		enable.software = true;
		enable.addfeeds = true;
		enable.disco = false;
		enable.redirect = false;

	} else if (this.type === 'global') {

		enable.title = false;
		enable.software = false;
		enable.addfeeds = false;
		enable.disco = false;
		enable.redirect = false;

	} else {
		console.log("UNKNOWN value of type")
	}



	if (enable.disco) {

		this.el.find('#discostorage').show();

		enabled.disco = this.el.find('#discoenable').prop('checked');
		if (enabled.disco) {
			this.el.find('#ep_host').show();
			this.el.find('#ep_entityid').show();
			this.el.find('#ep_response').show();
		} else {
			this.el.find('#ep_host').hide();
			this.el.find('#ep_entityid').hide();
			this.el.find('#ep_response').hide();
		}

	} else {
		this.el.find('#discostorage').hide();
		this.el.find('#ep_host').hide();
		this.el.find('#ep_entityid').hide();
		this.el.find('#ep_response').hide();
	}



	if (enable.redirect) {
		this.el.find('#ep_redirect').show();
	} else {
		this.el.find('#ep_redirect').hide();
	}



	if (enable.addfeeds) {
		this.el.find('#additionalfeeds').show();
		this.el.find('#additionalentities').show();
	} else {
		this.el.find('#additionalfeeds').hide();
		this.el.find('#additionalentities').hide();
	}


	if (enable.software) {
		this.el.find('#spsoftwareselection').show();
	} else {
		this.el.find('#spsoftwareselection').hide();
	}

	if (enable.title) {
		this.el.find('#titlediv').show();
	} else {
		this.el.find('#titlediv').hide();
	}


	if (enabled.disco) {
		var host = this.el.find('#ep_host input').val();
		if (host !== '') {
			if (this.el.find('#ep_entityid input').val() === '') {
				this.el.find('#ep_entityid input').val('https://' + host + '/sp-entityid');
			}
			if (this.el.find('#ep_response input').val() === '') {
				this.el.find('#ep_response input').val('https://' + host + '/response.html');
			}
			if (this.el.find('#ep_redirect input').val() === '') {
				this.el.find('#ep_redirect input').val('https://' + host + '/login?idp=');
			}
		} 
	}

		

	/**
	 * This section is about updating the docs. 
	 * the first section is just updating the view.
	 */

	var obj = {
		"type": this.type,
		"feeds": []
	};
	obj["title"] = this.el.find('#titlediv input').val();
	obj.ep_host 	= this.el.find('#ep_host input').val();
	obj.ep_entityid = this.el.find('#ep_entityid input').val();
	obj.ep_response = this.el.find('#ep_response input').val();
	obj.ep_redirect = this.el.find('#ep_redirect input').val();



	$("input[name=feed]:checked").each(function() {
		console.log("feed item ", this);
		obj.feeds.push($(this).attr('value'));
	})

	obj.feedsT = JSON.stringify(obj.feeds);


	$("#docout").empty();
	$("#docout").append('<pre>' + JSON.stringify(obj, undefined, 4) + '</pre>');

	if (this.type === 'embed') {

		this.template('doc/embed', function(template) {
			console.log("Applying template", template(obj));

			$("#docout").append(template(obj));
		});
		

	} else if (this.type === 'sp') {


	} else if (this.type === 'fed') {


	} else if (this.type === 'global') {



	} else {
		console.log("UNKNOWN value of type")
	}



}


App.prototype.template = function(name, callback) {
	$.get('../assets/js-customize/templates/' + name + '.html', function(source) {
		var template = Handlebars.compile(source);
		callback(template);
		// console.log("Loaded template", name, source)
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

		that.update();

	});


}




$(document).ready(function() {

	// alert("Customize");

	var a = new App($("div#customize"));




});