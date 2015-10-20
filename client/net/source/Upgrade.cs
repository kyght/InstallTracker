using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace com.Kyght.InstallTracker
{
    public class Upgrade : KyghtObject
    {
	    public Int64? upid { get; set; }
        public String product { get; set; }
        public String version { get; set; }
        public Int32? vernum { get; set; }
        public String custom { get; set; }
        public String url { get; set; }
        public String notesurl { get; set; }
    }
}
