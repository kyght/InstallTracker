using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Collections.Specialized;
using System.Net;
using Newtonsoft.Json;

namespace com.Kyght.InstallTracker
{
    public class TrackArgs
    {
        public object sender;
        public KyghtObject response;
        public String rawstring;
    }

    public class TrackFileArgs
    {
        public object sender;
        public byte[] data;
    }

    public class TrackRegArgs: TrackArgs
    {
        public Registration register;
    }

    public class TrackUpgradeArgs : TrackArgs
    {
        public Upgrade upgrade;
    }

    public delegate void OnDownloadComplete(TrackFileArgs args); 
    public delegate void OnTrackerComplete(TrackArgs args); 
    public delegate void OnRegistrationComplete(TrackRegArgs args);
    public delegate void OnUpgradeAvailable(TrackUpgradeArgs args); 

    public class Tracker
    {
        public String URL { get; set; }
        /// <summary>
        /// Product Name - 
        /// </summary>
        public String Product { get; set; }
        public String Version { get; set; }
        public String Custom { get; set; } 
        public String SecretKey { get; set; }
        public String RegID { get; set; }

        private List<KyghtWebClient> webRequests = new List<KyghtWebClient>();

        public Tracker(String url, String product, String version, String secret)
        {
            this.URL = url;
            this.Product = product;
            this.Version = version;
            this.SecretKey = secret;
        }

        public bool Register(String key, String name, String email, String contact, String phone, String address, String city, String state, String zipcode) {
            //Error Handler not added here so caller can deal with the real errors.

            var response = HttpHelper.Post(URL, new NameValueCollection() {
                { "action", "regupdate" },                
                { "sky", SecretKey },                
                { "product", Product },
                { "ver", Version },
                { "custom", Custom },

                { "trackid", RegID }, //TrackID from previous registration
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
                RegID = outObject.regID; //Return Registration Call for future updates
                return true;
            }

            return false;
        }

        public bool Usage(String key, OnTrackerComplete onComplete )
        {
            var pars = new NameValueCollection() {
                { "action", "useapp" },                
                { "sky", SecretKey },                
                { "product", Product },
                { "ver", Version },
                { "custom", Custom },

                { "trackid", RegID }, //TrackID from previous registration
                { "key", key }
            };

            if (onComplete != null)
            {
                KyghtWebClient kyw = HttpHelper.PostAsync(URL, pars, onUsageCompleteHandler);
                kyw.onComplete = onComplete;
                webRequests.Add(kyw);
                return true;
            }
            //If not callback provided then we will block on the call
            var response = HttpHelper.Post(URL, pars);

            var str = System.Text.Encoding.Default.GetString(response);
            if (str != null && str.Equals("0")) return false; //Secret key might not match
            var outObject = JsonConvert.DeserializeObject<KyghtObject>(str);
            if (outObject.valid == "TRUE")
            {
                return true;
            }
            //If this value is false then the user never registered, caller show try to re-register the client
            return false;
        }

        private void onUsageCompleteHandler(object sender, UploadValuesCompletedEventArgs e)
        {
            //Pop out of reference list
            KyghtWebClient kywc = sender as KyghtWebClient;
            if (kywc != null) webRequests.Remove(kywc);

            var str = System.Text.Encoding.Default.GetString(e.Result);
            if (str != null && str.Equals("0")) return; //Secret key might not match
            var outObject = JsonConvert.DeserializeObject<KyghtObject>(str);            
            if (kywc != null) kywc.onComplete(
                new TrackArgs
                 {  
                    response = outObject,
                    rawstring = str,
                    sender = this,
                 });
        }

        public bool UpgradeAvailable(OnUpgradeAvailable onUpgrade)
        {
            Upgrade upg = null;
            return UpgradeAvailable(out upg, onUpgrade);
        }
        public bool UpgradeAvailable(out Upgrade details, OnUpgradeAvailable onUpgrade)
        {
            var pars = new NameValueCollection() {
                { "action", "upgrade" },                
                { "sky", SecretKey },                
                { "product", Product },
                { "ver", Version },
                { "custom", Custom },
            };

            if (onUpgrade != null)
            {
                var kyw = HttpHelper.PostAsync(URL, pars, onUpgradeAvailableCompleteHandler);
                kyw.onUpgradeComplete = onUpgrade;
                webRequests.Add(kyw);
                details = null;
                return true;
            }

            var response = HttpHelper.Post(URL, pars);
            details = null;

            var str = System.Text.Encoding.Default.GetString(response);
            if (str != null && str.Equals("0")) return false; //Secret key might not match
            var outObject = JsonConvert.DeserializeObject<Upgrade>(str);
            if (outObject.valid == "TRUE")
            {
                details = outObject;
                return true;
            }
            //If this value is false then the user never registered, caller show try to re-register the client
            return false;
        }

        private void onUpgradeAvailableCompleteHandler(object sender, UploadValuesCompletedEventArgs e)
        {
            //Pop out of reference list
            KyghtWebClient kywc = sender as KyghtWebClient;
            if (kywc != null) webRequests.Remove(kywc);

            var str = System.Text.Encoding.Default.GetString(e.Result);
            if (str != null && str.Equals("0")) return; //Secret key might not match
            var outObject = JsonConvert.DeserializeObject<Upgrade>(str);
            if (outObject != null && !outObject.IsValid) return; //Their is not upgrade available

            if (kywc != null) kywc.onUpgradeComplete(
                new TrackUpgradeArgs
                {
                    upgrade = outObject,
                    response = outObject,
                    rawstring = str,
                    sender = this,
                });
        }

        public void Download(String fileurl, System.Net.DownloadDataCompletedEventHandler onComplete)
        {
            var wc = new System.Net.WebClient();
            wc.DownloadDataCompleted += new System.Net.DownloadDataCompletedEventHandler(onComplete);
            var furi = new Uri(fileurl);
            wc.DownloadDataAsync(furi);
        }


        public void Download(String fileurl, String filename, System.ComponentModel.AsyncCompletedEventHandler onComplete, DownloadProgressChangedEventHandler onProgress)
        {
            var wc = new System.Net.WebClient();
            wc.DownloadFileCompleted += new System.ComponentModel.AsyncCompletedEventHandler(onComplete);
            wc.DownloadProgressChanged += new DownloadProgressChangedEventHandler(onProgress);
            var furi = new Uri(fileurl);
            wc.DownloadFileAsync(furi, filename);
        }

    }
}
