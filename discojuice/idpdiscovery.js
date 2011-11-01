/*
 * IdP Discovery Service
 *
 * An implementation of the IdP Discovery Protocol in Javascript
 * 
 * Author: Andreas Åkre Solberg, UNINETT, andreas.solberg@uninett.no
 * Licence: LGPLv2
 */

var IdPDiscovery = function() {

	var acl = true;
	var returnURLs = [];
	var serviceNames = {};
	
	var query = {};
	(function () {
		var e,
			a = /\+/g,  // Regex for replacing addition symbol with a space
			r = /([^&;=]+)=?([^&;]*)/g,
			d = function (s) { return decodeURIComponent(s.replace(a, " ")); },
			q = window.location.search.substring(1);

		while (e = r.exec(q))
		   query[d(e[1])] = d(e[2]);
	})();
	
	function addQueryParam(url, key, value) {
		var delimiter = ( (url.indexOf('?') != -1) ? '&' : '?');		
		return url + delimiter + key + '=' + value;
	}
	
	return {
		
		"nameOf": function(entityid) {
			if (serviceNames[entityid]) return serviceNames[entityid];
			return entityid;
		},
		"getSP": function() {
			return (query.entityID || null);
		},
		"getName": function() {
			return this.nameOf(this.getSP());
		},
		
		// This function takes an url as input and returns the hostname.
		"getHostname" : function(str) {
			var re = new RegExp('^(?:f|ht)tp(?:s)?\://([^/]+)', 'im');
			return str.match(re)[1].toString();
		},
		
		"returnTo": function(e) {
			
			var returnTo = query['return'] || null;
			var returnIDParam = query.returnIDParam || 'entityID';
			var allowed = false;

			if(!returnTo) {
				DiscoJuice.Utils.log('Missing required parameter [return]');
				return;
			}
			if (!acl) {
				allowed = true;
			} else {

				
				var returnToHost = this.getHostname(returnTo);
				
				for (var i = 0; i < returnURLs.length; i++) {
					if (returnURLs[i] == returnToHost) allowed = true;

				}
				
				if (!allowed) {
					returnTo = addQueryParam(returnTo, 'error', encodeURIComponent('IdP Discovery: Access denied. Access not granted to return results to host [' + returnToHost + ']'));
					
					DiscoJuice.Utils.log('Access denied for return parameter [' + returnToHost + ']');
					DiscoJuice.Utils.log('Allowed hosts');
					DiscoJuice.Utils.log(returnURLs);
				}
			}
			

			
			// Return error with access denied.
			if (!allowed) {
				
				window.location = returnTo;
				
			// Return without entity found...
			} else if (!e.entityID) {
				DiscoJuice.Utils.log('ReturnTo without Entityid');
				DiscoJuice.Utils.log(e);
				window.location = returnTo;
			
			// Return entityid
			} else {
				
				if (e && e.auth) {
					returnTo = addQueryParam(returnTo, 'auth', e.auth);
				}
				
				DiscoJuice.Utils.log('ReturnTo with Entityid');
				window.location = addQueryParam(returnTo, returnIDParam, escape(e.entityID));
			}
			
			

		},
		
		"receive": function() {
		
			var entityID = this.getSP();

			if(!entityID) {
				// DiscoJuice.Utils.log('Missing required parameter [entityID]');
				return;
			}
			
			var preferredIdP = DiscoJuice.Utils.readCookie() || null;
			
			if (query.IdPentityID) {
				DiscoJuice.Utils.createCookie(query.IdPentityID);
				preferredIdP = query.IdPentityID;
			}
			
			var isPassive = query.isPassive || 'false';
			
			if (isPassive === 'true') {
				this.returnTo({'entityID': preferredIdP});
			}
		},
		
		"setup": function(options, rurls, servnames) {
			var that = this;
			
			if (servnames) {
				serviceNames = servnames;
			}
			
			console.log('Setting up DiscoJuice');
// 			console.log(rurls);
			returnURLs = rurls;

			this.receive();
			
			return function (e) {
				that.returnTo(e);
			};
		}
	};
}();

