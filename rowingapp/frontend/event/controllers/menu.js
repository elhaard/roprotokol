'use strict';

eventApp.controller(
  'menuCtrl', ['$scope','$location','$route','LoginService','$log',
               function ($scope,  $location,  $route,  LoginService, $log ) {
        $scope.activePath = null;
                 $scope.currentuser=LoginService.get_user();
                 $scope.ccurrentuser=LoginService.get_cuser();
                 $scope.$on('$routeChangeSuccess', function(){
                   $scope.activePath = $location.path();
                 });
      }
              ]
)

