  function pad(n) {
    if (n<10) return "0"+n;
    return ""+n;
  }
angular.module('dsrcommon.utilities.dsrtimeformat', []).filter('dsrtimeformat', function () {
  return function(tm,showdate) {
    //    var showdate=false;
    if (!(tm && tm.hour)) {
      return "";
    }
    if (showdate) {
      return pad(tm.day)+ "/" + pad(tm.month)+" "+pad(tm.year)+ " "+pad(tm.hour) + ":" + pad(tm.minute);
    } else {
      return pad(tm.hour) + ":" + pad(tm.minute);
    }
  };
});
