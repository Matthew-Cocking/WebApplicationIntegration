(function () {
    'use strict';
    /** Service to return the data */

    angular.module('ChiApp').service('applicationData',

        function ($rootScope) {
            var sharedService = {};
            sharedService.info = {};

            sharedService.publishInfo = function (key, obj) {
                this.info[key] = obj;
                $rootScope.$broadcast('systemInfo_' + key, obj);
            };

            return sharedService;
        }
    ).service('dataService',
            ['$q',
                '$http',
                '$routeParams',
                function ($q, $http, $routeParams) {
                    //Declares a few URLs that are used throughout - there are better ways to do this however hardcode was chosen because it was easy to understand
                    var urlBase3 = '../api/schedule';
                    var urlBase2 = '../api/presentations';
                    var urlBase = '../app/server/';

                    //gets information regarding presentations by using the appropriate URL to send to the front controller.
                    this.getPresentations = function () {
                        var defer = $q.defer();
                        $http.get(urlBase2, {cache: true}).then(function (response) {
                            defer.resolve({
                                //declares the objects to storee the data in
                                presentationCount: response.data.data.RowCount,
                                data: response.data.data.Result
                            });
                            //console.log(response);

                        }, function (err) {
                            defer.reject(err)
                        });
                        return defer.promise;

                    };

                    //gets information regarding categories by using the appropriate URL to send to the front controller.
                    this.getCategories = function () {
                        var defer = $q.defer();
                        $http.get(urlBase2 + '/categories', {cache: true}).then(function (response) {
                            defer.resolve({
                                categoryCount: response.data.data.RowCount,
                                data: response.data.data.Result
                            });
                            //console.log(response);

                        }, function (err) {
                            defer.reject(err)
                        });
                        return defer.promise;
                    };

                    //gets information regarding days of the week by using the appropriate URL to send to the front controller.
                    this.getDays = function () {
                        var defer = $q.defer();
                        $http.get(urlBase3 + '/days', {cache: true}).then(function (response) {
                            defer.resolve({
                                dayCount: response.data.data.RowCount,
                                data: response.data.data.Result
                            });
                            //console.log(response);
                        }, function (err) {
                            defer.reject(err);
                        });
                        return defer.promise;
                    };

//gets information regarding timeslots by using the appropriate URL to send to the front controller.
                    this.getTimeslots = function () {
                        var defer = $q.defer();

                        $http.get(urlBase3 + '/' + $routeParams.day, {cache: true}).                  // notice the dot to start the chain to success()
                            then(function (response) {
                                defer.resolve({
                                    data: response.data.data.Result,         // create data property with value from response
                                    rowCount: response.data.data.RowCount // create rowCount property with value from response
                                });
                                //console.log(response);
                            }, function (err) {
                                defer.reject(err);
                            });
                        // the call to getCourses returns this promise which is fulfilled
                        // by the .get method .success or .failure
                        return defer.promise;
                    };
//gets sessions within a specific time slot by using the appropriate URL to send to the front controller.
                    this.getSessions = function () {
                        var defer = $q.defer();

                        $http.get(urlBase3 + '/' + $routeParams.day + '/' + $routeParams.timeslot, {cache: true}).                  // notice the dot to start the chain to success()
                            then(function (response) {
                                defer.resolve({
                                    data: response.data.data.Result,         // create data property with value from response
                                    rowCount: response.data.data.RowCount // create rowCount property with value from response
                                });
                                //console.log(response);
                            }, function (err) {
                                defer.reject(err);
                            });
                        // the call to getCourses returns this promise which is fulfilled
                        // by the .get method .success or .failure
                        return defer.promise;
                    };
//gets details regarding a specific session by using the appropriate URL to send to the front controller.
                    this.getDetails = function () {
                        var defer = $q.defer();

                        $http.get(urlBase3 + '/' + $routeParams.day + '/' + $routeParams.timeslot + '/' + $routeParams.session, {cache: true}).                  // notice the dot to start the chain to success()
                            then(function (response) {
                                defer.resolve({
                                    data: response.data.data.Result,         // create data property with value from response
                                    rowCount: response.data.data.RowCount // create rowCount property with value from response
                                });
                                //console.log(response);
                            }, function (err) {
                                defer.reject(err);
                            });
                        // the call to getCourses returns this promise which is fulfilled
                        // by the .get method .success or .failure
                        return defer.promise;
                    };

                    //Determines system information which is taken from appInfo.json file
                    this.getSysInfo = function () {
                        var defer = $q.defer(),             // The promise
                            infoUrl = urlBase + 'appInfo.json';

                        $http.get(infoUrl, {cache: true}).                          // notice the dot to start the chain to success()
                            then(function (response) {
                                defer.resolve({
                                    title: response.data.title,         // create data property with value from response
                                    author: response.data.author  // create rowCount property with value from response
                                });
                                //console.log(response);
                            }, function (err) {
                                defer.reject(err);
                            });

                        return defer.promise;
                    };
                    //Used to update the chair of a session
                    this.updateDetail = function (selectedDetail) {
                        var defer = $q.defer();

                        $http.post('/update', selectedDetail, {cache: true}).then(function (response) {
                            defer.resolve(response);
                            //console.log(response);
                        }, function (err) {
                            defer.reject(err);
                        });
                        window.location.reload();
                        return defer.promise;
                    };

                    //Declare the buttons within the navbar using navitems.json file
                    this.getNavitems = function () {
                        var defer = $q.defer(),
                            navitemsURL = urlBase + 'navitems.json';

                        $http.get(navitemsURL, {cache: false}).then(function (response) {
                            defer.resolve({
                                data: response.data,         // create data property with value from response
                            });
                            ////console.log(response);
                        }, function (err) {
                            defer.reject(err);
                        });

                        return defer.promise;
                    }

                }
            ]
        );
}());