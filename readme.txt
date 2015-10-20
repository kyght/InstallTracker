=== Install Tracker ===
Contributors: Kyght
Tags: install, distribution, usage, track, upgrade
Donate link: http://www.kyght.com/
Requires at least: 4.3
Tested up to: 4.3
License: GPL v3
License URI: www.kyght.com/gpl.txt

Tracks the distribution of your installable product. Usage statistics, Registrations and Upgrades.

== Description ==
Tracks the distribution of your installable products. The plugin provides an ajax API to accept installation registrations & usage data. Also provides a mechanism for pushing product upgrades to clients.  Custom build tags can also be used to track clients with customized products and push custom upgrades to these clients.

Plugin REST API
    http://www.yoursite.com/wp-admin/admin-ajax.php


    Actions:
       	regupdate  - Adds\Updates a registration record
	useapp     - Updates product\version usage and registered client use
	upgrade    - Checks for a product upgrade with a higher version number


    Example (.Net API call to register a client)

            var response = HttpHelper.Post(URL, new NameValueCollection() {
                { "action", "regupdate" },                
                { "sky", SecretKey },  //Secret ID must match ID in your Wordpress|Settings|InstallTracker              
                { "product", Product },
                { "ver", Version },
                { "custom", Custom },

                { "trackid", RegID }, //TrackID from previous registration to allow updates, blank for new
                { "key", key },
                { "name", name },
                { "email", email },
                { "phone", phone },
                { "contact", contact },
                { "addr", address },
                { "city", city },
                { "state", state },
                { "zipcode", zipcode}
            });    
        
            var str = System.Text.Encoding.Default.GetString(response);
            if (str != null && str.Equals("0")) return false; //Secret key might not match
            var outObject = JsonConvert.DeserializeObject<Registration>(str);
            if (outObject.valid == "TRUE")
            {
                RegID = outObject.regID; //Returned Registration ID for future updates
                return true;
            }    


.Net API wrapper client library is available at www.kyght.com (http://www.kyght.com/?page_id=147). 
Other client libraries will be available later on GitHub (https://github.com/kyght/InstallTracker).

.Net client dll Usage
------------------------
installTrack = new Tracker(\"http://www.yoursite.com/wp-admin/admin-ajax.php\", \"YourProduct\", \"2.1.16.80\", \"666745\");

  * installTrack.Register(String key, String name, String email, String contact, String phone, String address, String city, String state, String zipcode)
  * installTrack.Usage(String key, OnTrackerComplete onComplete )
  * installTrack.UpgradeAvailable(OnUpgradeAvailable onUpgrade)
  * installTrack.Download(String fileurl, String filename, System.ComponentModel.AsyncCompletedEventHandler onComplete, DownloadProgressChangedEventHandler onProgress)


== Installation ==
1. Upload `kyght-installtrack` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the \'Plugins\' menu in WordPress

== Frequently Asked Questions ==
= How do I call the functions =

You can use any language that provides HTTP POST functionality.

= What are the functions available =

I have provided a .NET library, including source code that you can call from your installed application.

= Can I use this from mobile applications =

Yes, while App Stores do provide stats on number of installs, this doesn\'t include all users across all platforms.

== Screenshots ==
1. Summary view of data collected
2. List of products \\ versions and their usage
3. List of registrations received

== Changelog ==
Initial version
