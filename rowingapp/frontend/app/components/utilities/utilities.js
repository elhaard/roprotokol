/*jslint node: true */
'use strict';

var right2dkm;

var side2dk = {
  'left':'venstre',
  'right':'højre',
  'center':'midtfor'
}

var right2dk;

var subject2dk = {
  'all':'alle',
  'cox':'styrmanden',
  'none':'ingen',
  'any':'mindst een'
}


var make_rights = function(db_service){
    if (db_service && ! (right2dkm && right2dk)) {
        var rights = db_service.getDB('memberrighttypes');
        if (rights && rights.length) {
            right2dk = {};
            right2dkm = {};

            for (var i = 0; i < rights.length; i++) {
                var r = rights[i];
                right2dk[r.member_right] = r.showname;
                right2dkm[r.member_right] = r.predicate;
            }
        }
    }
}

angular.module('rowApp.utilities.urldecode', []).filter('urldecode', function () {
  return function (text) {
    return window.decodeURIComponent(text);
  };
});

angular.module('rowApp.utilities.urlencode', []).filter('urlencode', function () {
  return function (text) {
    return window.encodeURIComponent(text);
  };
});

angular.module('rowApp.utilities.nodsr', []).filter('nodsr', function () {
  return function (text) {
    if (text === "DSR") {
      return "";
    } else {
      return text;
    }
  };
});

angular.module('rowApp.utilities.mtokm', []).filter('mtokm', function () {
  return function (meters) {
    return (meters / 1000).toFixed(1);
  };
});

angular.module('rowApp.utilities.rightreqs', []).filter('rightreqs', ['DatabaseService', function (db_service) {
  make_rights(db_service);
  var ss={'cox':'styrmanden','all':'alle','any':'mindst en','forbidden':'forbudt'};
  return function (rights) {
    var res="";
    angular.forEach(rights, function (subject,right) {
        if (res!="") {
          res +=", ";
        }
      if (subject=='none') {
        res+=(" ingen må "+(right2dkm[right]?right2dkm[right]:right));        
      } else {
        res+=(ss[subject]+" skal "+(right2dkm[right]?right2dkm[right]:right));
      }
    },this);
    return res==""?"ingen krav":res;
  };
}]);

angular.module('rowApp.utilities.subjecttodk', []).filter('subjecttodk', function () {
  return function (sb) {
    var r=subject2dk[sb];
    return r?r:sb;
  };
});

var damage_degrees={
  //  0: '\u2713', // until we make it work on windows
  0: ' ',
  1: 'Let skadet',
  2: 'Middel skadet',
  3: 'Svært skadet',
  4: 'Vedligehold'
}

var dktags={
  'intime': 'ind',
  'outtime': 'ud',
  'destination': 'destination',
  'triptype': 'turtype',
  'rowers': 'roere',
  'boat': 'båd'
}


angular.module('rowApp.utilities.damagedegreedk', []).filter('damagedegreedk', function () {
  return function (dd) {
    var r=damage_degrees[dd];
    return r?r:dd;
  };
});

angular.module('rowApp.utilities.dk_tags', []).filter('dk_tags', function () {
  return function (tag) {
    var r=dktags[tag];
    return r?r:tag;
  };
});

angular.module('rowApp.utilities.righttodk', []).filter('righttodk', ['DatabaseService', function (db_service) {
  make_rights(db_service);

  return function (sb) {
    var r=right2dk[sb];
    return (r?r:sb);
  };
}]);

angular.module('rowApp.utilities.argrighttodk', []).filter('argrighttodk', ['DatabaseService', function (db_service) {
  make_rights(db_service);

  return function (sb) {
    var r=right2dk[sb.member_right];
    var rr=r?r:sb;
    if (sb.arg) {
      rr=rr+" ("+sb.arg+")";
    }
    return rr;
  };
}]);

angular.module('rowApp.utilities.sidetodk', []).filter('sidetodk', function () {
  return function (sd) {
    var r=side2dk[sd];
    return (r?r:sd);
  };
});

angular.module('rowApp.utilities.leveltodk', []).filter('leveltodk', function () {
  return function (lvl) {
    return (lvl?"hylde "+lvl:"gulv");
  };
});

angular.module('rowApp.utilities.rowtodk', []).filter('rowtodk', function () {
  return function (rw) {
    if (!rw) return("");
    if (rw==1) return ("mod porten");
    if (rw==2) return ("inderst");
    return (rw);
  };
});


angular.module('rowApp.utilities.totime', []).filter('totime', function () {
  return function(hours) {
    var hrs = Math.floor(hours);
    var min = Math.round(hours % 1 * 60);
    min = min < 10 ? "0"+min : min.toString();
    return hrs + ":" + min;
  };
});


angular.module('rowApp.utilities.txttotime', []).filter('txttotime', function () {
  return function(txt) {
    if (!txt) return null;
    var t=txt.split(/[- :T]/);
    var dd=new Date(t[0], t[1]-1, t[2], t[3]||0, t[4]||0, t[5]||0);
    return dd;
  };
});

angular.module('rowApp.utilities.ifNull', []).filter('ifNull', function () {
  return function( val, defaultVal, suffix) {
    if (val === null) return defaultVal;
    if (suffix != null) {
      val += suffix;
    }
    return val;
  };
});

angular.module('rowApp.utilities.subArray', []).filter('subArray', function () {
  return function( arr, start, len) {
    if (! arr.splice ) {
      console.log("subArray input cannot be spliced", arr);
      return null;
    }
    if (start == null) {
      start = 0;
    }
    return arr.splice(start, len);
  };
});


angular.module('rowApp.utilities.keys', []).filter('keys', function () {
  return function( obj) {
    return Object.keys(obj);
  };
});



angular.module('rowApp.utilities.onlynumber', []).directive('onlynumber', function () {
  return {
    restrict: 'EA',
    require: 'ngModel',
    link: function (scope, elem, attrs, ngModel) {

      function checknumber() {
        var et=elem.val();
        if (et==null) return;
        if (et.length === 0) return;
        if (isNaN(et)) {
          et=et.replace(",",".").replace(/[^0-9\.]/g,"").replace(".","D").replace("."," ").replace("D",".");
          if (et===".") {
            et="0.";
          }
          elem.val(et);
          ngModel.$setViewValue(et.trim());
        }
      }
      
      scope.$watch(attrs.ngModel, function(newValue, oldValue) {
        checknumber();

                              
      });
    }
  };
}
                                                          )

angular.module('rowApp.utilities.transformkm', []).directive('transformkm', function () {
  return { 
    restrict: 'A',
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      if (ngModel) { // Don't do anything unless we have a model
        ngModel.$parsers.push(function (val) {
          if (val !== undefined) {
            var fval=val;
            if (typeof fval == 'string') {
              fval = val.replace(',', '.');
            }
            return fval * 1000;
          }
        });
        ngModel.$formatters.push(function (val) {
          if (val !== undefined) {
            var fval=val;
            if (typeof val == 'string') {
              fval = val.replace(',', '.');
            }
            return fval / 1000;
          }
        });
      }
    }
  };
});


angular.module('rowApp.utilities', [
  'rowApp.utilities.onlynumber',
  'rowApp.utilities.urldecode',
  'rowApp.utilities.urlencode',
  'rowApp.utilities.nodsr',
  'rowApp.utilities.sidetodk',
  'rowApp.utilities.leveltodk',
  'rowApp.utilities.rowtodk',
  'rowApp.utilities.transformkm',
  'rowApp.utilities.mtokm',
  'rowApp.utilities.rightreqs',
  'rowApp.utilities.subjecttodk',
  'rowApp.utilities.righttodk',
  'rowApp.utilities.argrighttodk',
  'rowApp.utilities.dk_tags',
  'rowApp.utilities.damagedegreedk',
  'rowApp.utilities.txttotime',
  'rowApp.utilities.totime',
  'rowApp.utilities.ifNull',
  'rowApp.utilities.subArray',
]).value('version', '0.2');
