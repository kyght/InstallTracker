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

.Net client library is available at www.kyght.com . Other client libraries will be available later.

Usage:
------------------------
installTrack = new Tracker(\"http://www.payrollInvoicing.com/wp-admin/admin-ajax.php\", \"PayrollInvoicing.com\", \"2.1.16.80\", \"666745\");
installTrack.RegID = PreviousRegID; //For updating registration


//Usage stats update product usage and registered client usage,
// if client is not registered yet, product stats will still be tracked
installTrack.Usage(PreviousRegID,
                    delegate(TrackArgs args)
                    {
                        //If false then we may have not registered yet
                        IsRegistered = args.response.IsValid;
                    });

//You can check for available upgrades and prompt use to download
 installTrack.UpgradeAvailable(
      delegate(TrackUpgradeArgs args) {
          var upg = args.upgrade;
          if (MessageBox.Show(\"Upgrade available, would you like to download & install?\",
              \"Upgrade\", MessageBoxButton.YesNo, MessageBoxImage.Question) ==
               MessageBoxResult.Yes)
                {
                     UpgradeFileName = System.IO.Path.GetTempPath() + \"\\\\\" +
                                   System.IO.Path.GetFileName(upg.url);
                     installTrack.Download(upg.url, UpgradeFileName,
                              TrackerUpgrade_DownloadFileCompleted,
                              TrackerUpgrade_DownloadProgress);
                 }
         }
);


private String UpgradeFileName = null;
void TrackerUpgrade_DownloadFileCompleted(object sender,
                     System.ComponentModel.AsyncCompletedEventArgs e)
{
      if (MessageBox.Show(\"Upgrade downloaded, your application will be restarted
                          to install the update?\", \"Upgrade\", MessageBoxButton.OK,
                          MessageBoxImage.Information) == MessageBoxResult.OK)
      {
           if (UpgradeFileName != null)
           {
                 //We need to Launch the Upgrade
                 Process process = new Process();
                 // Configure the process using the StartInfo properties.
                 process.StartInfo.FileName = UpgradeFileName;
                 //We need to run our of process because we are going to
                 //shutdown to allow the update of files
                 process.StartInfo.UseShellExecute = true;
                 process.Start();
                 //Sleep until process has had a chance to start
                 System.Threading.Thread.Sleep(2000);
                 Environment.Exit(2);
          }
     }
}

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
