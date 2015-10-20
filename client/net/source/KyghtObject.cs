using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace com.Kyght.InstallTracker
{
    public class KyghtObject
    {
        public String valid { get; set; }
        public String msg { get; set; }
        public bool IsValid
        {
            get {
                if (String.IsNullOrWhiteSpace(valid)) return false;
                return valid.ToUpper().Equals("TRUE");
                }
        }
    }
}
