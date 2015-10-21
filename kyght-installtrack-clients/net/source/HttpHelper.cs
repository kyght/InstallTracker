using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Collections.Specialized;
using System.Net;

namespace com.Kyght.InstallTracker
{
    public class KyghtWebClient : WebClient
    {
        public OnTrackerComplete onComplete;
        public OnUpgradeAvailable onUpgradeComplete;
        public OnRegistrationComplete onRegComplete;
    }

    public static class HttpHelper
    {
        public static byte[] Post(string uri, NameValueCollection pairs)
        {
            byte[] response = null;
            using (WebClient client = new WebClient())
            {
                response = client.UploadValues(uri, pairs);
            }
            return response;
        }

        public static KyghtWebClient PostAsync(string uri, NameValueCollection pairs, UploadValuesCompletedEventHandler onComplete)
        {
            KyghtWebClient client = new KyghtWebClient();
            var url = new Uri(uri);
            client.UploadValuesCompleted += onComplete;
            client.UploadValuesAsync( url, pairs);
            return client;
        }

    }
}
