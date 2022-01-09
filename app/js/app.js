(function () {
    "use strict";  // turn on javascript strict syntax mode
    angular.module("ChiApp",
        [
            'ngRoute'   // the only dependency at this stage, for routing
        ]
    ).              // note this fullstop where we chain the call to config
        config(
            [
                '$routeProvider',     // built in variable which injects functionality, passed as a string
                function($routeProvider) {
                    $routeProvider.
                    when('/presentations', { //Provides all of the routes that will be used throughout the frontend application
                        templateUrl: 'js/partials/presentation-list.html',
                        controller: 'PresentationController'
                    }).
                    when('/schedule', {
                        templateUrl: 'js/partials/day-list.html',
                        controller: 'DayController',
                    }).
                    when('/schedule/:day', {
                        templateUrl: 'js/partials/time-slots.html',
                        controller: 'TimeslotController'
                        }).
                    when('/schedule/:day/:timeslot', {
                            templateUrl: 'js/partials/session-list.html',
                            controller: 'SessionController'
                        }).
                    when('/schedule/:day/:timeslot/:session', {
                        templateUrl: 'js/partials/session-details.html',
                        controller: 'SessionDetailController'
                    }).
                    otherwise({
                        redirectTo: '/'
                    });
                }
            ]
        );  // end of config method
}());   // end of IIFE

function run($rootScope, $http, $location, $localStorage) {
    // keep user logged in after page refresh
    if ($localStorage.currentUser) {
        $http.defaults.headers.common.Authorization = 'Bearer ' + $localStorage.currentUser.token;
    }
}