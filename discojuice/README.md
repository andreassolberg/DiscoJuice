# Example for using the Shibboleth SP DiscoFeed for metadata filtering

```javascript

// base path of your application
var appUrl = "https://www.example.com";

// url to redirect to after the SSO login process was completed
var targetUrl = appUrl + "/login/shibboleth";

// setup discojuice with metadata filtering using the Shibboleth DiscoFeed
$('#disco-juice-login-link')
        .DiscoJuice({
                "title"    : "Shibboleth Login",
                "subtitle" : "Please choose your organization from the list",

                "cookie"   : false,
                "location" : true,
                "country"  : false,
                //"countryAPI" : "https://cdn.discojuice.org/country",

                "discoPath"  : "https://cdn.discojuice.org/",
                "redirectURL": appUrl + "/Shibboleth.sso/Login?target=" + encodeURIComponent(targetUrl) + "&entityID=",
                "spentityid" : appUrl + "/Shibboleth.sso/Metadata",
                "discofeed"  : appUrl + '/Shibboleth.sso/DiscoFeed', //FFX
                "metadata"   : ['https://cdn.discojuice.org/feed/dfn'], // DFN-AAI

                "callback" : function (e, djc) {
                        var returnto = window.location.href;
                        var options = djc.parent.Utils.options;
                        var redirectURL = options.get('redirectURL');
                        if (redirectURL) {
                                var newlocation = redirectURL + escape(e.entityID);
                                //console.log("Redirecting to " + newlocation);
                                window.location = newlocation;
                        }
                        else alert("DiscoJuice is misconfigured. You need to provide a redirectURL to send the user when a provider is selected."); 
                },

        }); 
```
