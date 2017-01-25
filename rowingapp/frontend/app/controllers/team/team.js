'use strict';

gymApp.controller(
  'teamCtrl',
  ['$scope', '$routeParams', 'DatabaseService', '$filter', 'ngDialog','$log',
   function ($scope, $routeParams, DatabaseService, $filter, ngDialog, $log) {
     $scope.attendance = [];
     $scope.quarters = [1,2,3,4];
     $scope.todpattern="[0-2]\\d:[0-5]\\d";
     $scope.weekdays=["mandag","tirsdag","onsdag","torsdag","fredag","lørdag","søndag"];
     DatabaseService.init({"team":true,"member":true}).then(function () {
       $scope.teams = DatabaseService.getDB('team/team');
     });
     
     DatabaseService.getDataNow("team/attendance", null, function (res) {
       $scope.attendance=res.data;         
     }
                               );
     $scope.getRowerByName = function (val) {
       return DatabaseService.getRowersByNameOrId(val, undefined);
     };

     $scope.setTeam = function (tm) {
       $scope.currentteam=tm;
     }
       
     $scope.addTeam = function() {
       $log.debug("add team");
       DatabaseService.addTeam($scope.newteam).promise.then(
         function(st) {
           $scope.teams.push($scope.newteam)
         }
       );
     }

     $scope.deleteTeam = function(tm) {
       $log.debug("delete team");
       DatabaseService.deleteTeam(tm).promise.then(
         function(st) {
           $scope.teams.splice($scope.teams.indexOf(tm),1);
         }
       );
     }

     $scope.attend = function() {
       if ($scope.currentteam) {
         $scope.checkout = {
           'member' : $scope.attendee,
           'team' : $scope.currentteam,
           'destination': {'distance':999},
           'comments':''
         }
         DatabaseService.attendTeam($scope.checkout).promise.then(
           function(st) {
             if (st.status=="ok") {
               $scope.attendance.push( {'team': $scope.currentteam.name, membername:$scope.attendee.name, memberid:$scope.attendee.id});
             } else if (st.status.search("Duplicate entry")) {
               $scope.message="Allerede tilmeldt";
             }
           }
         )
       }
     }
   }
  ]
);