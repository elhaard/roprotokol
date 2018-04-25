'use strict';

angular.module('eventApp', [
  'ngRoute',
  'dsrcommon.utilities.onlynumber',
  'dsrcommon.utilities.transformkm',
  'dsrcommon.utilities.safefilename',
  'dsrcommon.utilities.mtokm',
  'dsrcommon.utilities.urlencode',
  'ngSanitize',
  'ui.bootstrap',
  'ui.select',
  'angular-momentjs',
  'ngDialog',
  'ngTable',
  'eventApp.version',
  'eventApp.database',
  'angular-confirm',
  'ui.bootstrap',
  'ui.bootstrap.datetimepicker',
  'angular.filter',
  'checklist-model',
  'ngFileUpload'
])
.config([
  '$locationProvider', function($locationProvider) {
    $locationProvider.html5Mode(true);
  }])

.config([
  '$routeProvider', function($routeProvider) {
    $routeProvider.when('/eventsubscribe/', {
      templateUrl: 'templates/timeline.html',
      controller: 'eventCtrl'
    });
    $routeProvider.when('/forumsubscribe/', {
      templateUrl: 'templates/forum.html',
      controller: 'eventCtrl'
	});
    $routeProvider.when('/overview/', {
      templateUrl: 'templates/overview.html',
      controller: 'eventCtrl'
	});
    $routeProvider.when('/eventcreate/!#forum/:forum', {
      templateUrl: 'templates/eventcreate.html',
      controller: 'eventCtrl'
	});
    $routeProvider.when('/eventcreate/', {
      templateUrl: 'templates/eventcreate.html',
      controller: 'eventCtrl'
	});
    $routeProvider.when('/!#message/:message', {
      templateUrl: 'templates/message.html',
      controller: 'eventCtrl'
	});
    $routeProvider.when('/message/', {
      templateUrl: 'templates/message.html',
      controller: 'eventCtrl'
	});
    $routeProvider.when('/admin/', {
      templateUrl: 'templates/admin.html',
      controller: 'eventCtrl'
	});
    $routeProvider.when('/public/', {
      templateUrl: 'templates/public.html',
      controller: 'eventCtrl'
	});
    $routeProvider.when('/about/', {
      templateUrl: 'templates/about.html',
      controller: 'noRight'
	});
    $routeProvider.when('/!#timeline/:event', {
      templateUrl: 'templates/timeline.html',
      controller: 'eventCtrl'
    });
    $routeProvider.when('/timeline/', {
      templateUrl: 'templates/timeline.html',
      controller: 'eventCtrl'
    });
    $routeProvider.when('/member/:memberid', {
      templateUrl: 'templates/member.html',
      controller: 'eventCtrl'
    });
    $routeProvider.when('/member/', {
      templateUrl: 'templates/member.html',
      controller: 'eventCtrl'
    });
    $routeProvider.when('/showevent/:event', {
      templateUrl: 'templates/timeline.html',
      controller: 'eventCtrl'
    });
    $routeProvider.when('/forcelogin/', {
      templateUrl: 'templates/login.html',
      controller: 'eventCtrl'
	});
    $routeProvider.when('/login/', {
      templateUrl: 'templates/login.html',
      controller: 'noRight'
	});
    $routeProvider.when('/', {redirectTo: '/login'});
    $routeProvider.otherwise({
      templateUrl: 'templates/notimplementet.html',
      controller: 'noRight'
    });
  }]).config(['uiSelectConfig', function(uiSelectConfig) {
    uiSelectConfig.theme = 'bootstrap';
  }])
//  .config(['http', function(httpConfig) {  httpConfig.headers.common['Content-Type'] = 'application/json; charset=utf-8'; }])
;

