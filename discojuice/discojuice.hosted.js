/*
 * DiscoJuice
 * Author: Andreas Åkre Solberg, UNINETT, andreas.solberg@uninett.no
 * Licence undecided.
 */
if (typeof DiscoJuice == "undefined") var DiscoJuice = {};

function getConfig (config) {
	var options, i;
	
	options = {
		"title": "Select provider",
		"cookie": true,
		"country": true,
		"location": true,
		"countryAPI": "https://cdn.discojuice.org/country",
		"discoPath": "https://cdn.discojuice.org/",
		"callback": function (e, djc) {
			alert("DiscoJuice is misconfigured. You need to provide a redirectURL to send the user when a provider is selected.");
		},
		"metadata": []
	};

	if (config.hasOwnProperty("title")) {
		options.title = "Sign in to <strong>" + config.title + "</strong>";
		options.subtitle = "Select your Provider";
	}
	if (config.hasOwnProperty("spentityid") && config.hasOwnProperty("responseurl")) {
		options.disco = {
			"spentityid": config.spentityid,
			"url": config.responseurl,
			"stores": ["https://cdn.discojuice.org/store"],
			"writableStore": "https://cdn.discojuice.org/store"
		};
	}
	if (config.hasOwnProperty("redirectURL")) {
		options.callback = function (e, djc) {
			var returnto = window.location.href;
			window.location = config.redirectURL + escape(e.entityID);
		};
	}
	if (config.hasOwnProperty("feeds")) {
		for(i = 0; i < config.feeds.length; i++) {
			options.metadata.push("https://cdn.discojuice.org/feeds/" + config.feeds[i]);
		}
	}
	return options;
}


DiscoJuice.Hosted = {
	
	"getConfig": getConfig,
	"setup": function (config) {
		var options;
		var t = "body";

		options = getConfig(config);

		if (config.hasOwnProperty("target")) {
			t = config.target;
		} else {
			options.always = true;
		}


		
		$(document).ready(function() {
			$(t).DiscoJuice(options);
			// console.log("SETUP completed");
			// console.log(options);
		});
		
	}
};
