'use strict';

app.controller(
    'BoathallCtrl',
    ['$scope', 'DatabaseService', 'NgTableParams', '$filter', '$route', '$confirm','$log',
     function ($scope, DatabaseService, NgTableParams, $filter,$route,$confirm,$log) {

       DatabaseService.init({"boat":true}).then(function () {
         $scope.allboats = DatabaseService.getBoats();
         $scope.iboats=DatabaseService.getDB('boats');
         $scope.locations = DatabaseService.getDB('locations');
         $scope.config={'headers':{'XROWING-CLIENT':'ROPROTOKOL'}};

         $scope.plan = {};

      });
     }]
);
