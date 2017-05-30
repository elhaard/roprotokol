'use strict';
angular.module('coxApp.database.basic-services', []).service('BasicService', function($http, $q,$log) {

this.getpw = function(data) {
    $http.post('../../../public/getpw.php', data).then(function(r) {
      alert("password er sendt");
    },function(r) {
      alert("det mislykkedes at sende nyt password");
    });
}

this.setpw = function(data) {
    $http.post('../../../backend/event/setpw.php', data).then(function(r) {
      alert("password skiftet");
    },function(r) {
      alert("det mislykkedes at skifte password");
    });
}


  this.logout = function() {
    $http.get('../../../backend/cox/aspirants/logout.php').then(function(r) {
      alert("logget ud");
    },function(r) {
      alert("logget ud problem");
    });
  }
  
});
