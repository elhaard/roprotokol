'use strict';
angular.module('coxApp.database.basic-services', []).service('BasicService', function($http, $q,$log) {

this.getpw = function(data) {
    $http.post('../../../public/getpw.php', data).then(function(r) {
    },function(r) {
      alert("det mislykkedes at sende nyt password");
    });
  }
});
