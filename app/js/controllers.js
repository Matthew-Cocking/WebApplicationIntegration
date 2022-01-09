(function () {
    "use strict";

    angular.module('ChiApp').
        //Declares a controller for the Index Page
        controller('IndexController',
            [
                '$scope',//Variables are declared that can be used throughout this controller
                'dataService',
                'applicationData',
                function ($scope, dataService) {
                    var getSysInfo = function () { // determines the system info by checking the response given by services
                        dataService.getSysInfo().then(
                            function (response) {
                                $scope.title = response.title;
                                $scope.author = response.author;
                                $scope.$on('systemInfo_session', function (ev, session) {
                                    $scope.chair = session.chair;
                                });
                            },
                            function (err) {
                                $scope.status = 'Unable to load data ' + err;
                            },
                            function (notify) {
                                //console.log(notify);
                            }
                        ); // end of getCourses().then
                    };

                    var getNavitems = function () { // Determines the nav bar by checking the response from services
                        dataService.getNavitems().then(
                            function (response) {
                                $scope.navitems = response.data;
                            },
                            function (err) {
                                $scope.status = 'Unable to load data ' + err;
                            },
                            function (notify) {
                                //console.log(notify);
                            }
                        ); // end of getCourses().then
                    };

                    getNavitems();
                    getSysInfo(); //Makes sure that these functions are executed

                }
            ]
        ).controller('LoginController', //Declare a controller for authenticating and logging in via the API
        [
            '$scope',
            'dataService',
            'applicationData',
            '$location',
            '$http',
            function ($scope, dataService, applicationData, $location, $http) {
                $scope.login = function (user) {
                    ////console.log(user);
                    $http.put('/login', user).then(function (response) {
                        if (response.data.token) {
                            //console.log(response);
                            //console.log("Login Successful");
                            $scope.loginFailed = false;
                            sessionStorage.userService = angular.toJson(response.data.token);
                            $http.defaults.headers.common.Authorization = 'Bearer ' + response.data.token;
                            $scope.successful = sessionStorage.userService;
                        } else {
                            //console.log(response);
                            $scope.loginFailed = true;
                            //console.log("Login Failed");
                        }
                    });

                    $scope.logout = function () {
                        sessionStorage.clear();
                        $scope.successful = false;
                        $http.defaults.headers.common.Authorization = ''
                    }
                };

                window.onload = function () {
                    if (sessionStorage.getItem("userService")) {
                        sessionStorage.removeItem("userService");
                    }
                }
            }
        ]
    ).controller('PresentationController', //Declares a controller for the Presentations
        [ //Variables are declared that can be used throughout this controller
            '$scope',
            'dataService',
            'applicationData',
            '$location',
            '$http',
            function ($scope, dataService, applicationData, $location, $http) {
                var getCategories = function () {
                    dataService.getCategories().then(
                        function (response) {
                            $scope.categoryCount = response.categoryCount;
                            $scope.categorys = response.data;
                        },
                        function (err) {
                            $scope.status = 'Unable to load data ' + err;
                        },
                        function (notify) {
                            //console.log(notify);
                        }
                    );
                };

                $scope.categorySelected = function () {
                    //console.log($scope.categorySelect.type);
                    $http.get('/api/presentations/category/' + $scope.categorySelect.type).then(function (response) {
                        $scope.presentations = response.data.data.Result;
                        //console.log(response);
                    });
                };

                var getPresentations = function () {
                    dataService.getPresentations().then(
                        function (response) {
                            $scope.presentationCount = response.presentationCount;
                            $scope.presentations = response.data;
                        },
                        function (err) {
                            $scope.status = 'Unable to load data ' + err;
                        },
                        function (notify) {
                            //console.log(notify);
                        }
                    );
                };
                getPresentations();
                getCategories();
            }
        ]
    ).controller('DayController', //Declares a controller to manage the Days
        [ //Variables are declared that can be used throughout this controller
            '$scope',
            'dataService',
            'applicationData',
            '$location',
            function ($scope, dataService, applicationData, $location) {
                var getDays = function () {
                    dataService.getDays().then(
                        function (response) {
                            $scope.dayCount = response.dayCount;
                            $scope.days = response.data;
                        },
                        function (err) {
                            $scope.status = 'Unable to load data ' + err;
                        },
                        function (notify) {
                            //console.log(notify);
                        }
                    );
                };

                var dayInfo = $location.path().substr(1).split('/');
                if (dayInfo.length === 2) {
                    $scope.selectedDay = {day: dayInfo[1]};
                }

                applicationData.publishInfo('day', {});
                $scope.selectedDay = {};

                $scope.selectDay = function ($event, day) {
                    $scope.selectedDay = day;
                    $location.path('schedule/' + day.day);
                    applicationData.publishInfo('day', day);
                };
                getDays();
            }
        ]
    ).controller('TimeslotController', //Declares a controller to manage the timeslots based on the chosen day
        [ //Variables are declared that can be used throughout this controller
            '$scope',
            'dataService',
            'applicationData',
            '$routeParams',
            '$location',
            function ($scope, dataService, applicationData, $routeParams, $location) {
                $scope.timeslots = [];
                let getTimeslots = function (day) {
                    dataService.getTimeslots(day).then(
                        function (response) {
                            $scope.timeslots = response.data;
                            $scope.timeslotCount = response.rowCount;
                        },
                        function (err) {
                            $scope.status = 'Unable to load data ' + err;
                        }
                    );
                };
                if ($routeParams && $routeParams.day) {
                    //console.log($routeParams.day);
                    getTimeslots($routeParams.day);
                    $scope.show = true;
                }


                var timeslotInfo = $location.path().substr(1).split('/');
                if (timeslotInfo.length === 2) {
                    $scope.selectedTimeslot = {id: timeslotInfo[1]};
                }

                $scope.selectedTimeslot = {};


                $scope.selectTimeslot = function ($event, timeslot) {
                    $scope.selectedTimeslot = timeslot;
                    $location.path('schedule/' + $routeParams.day + '/' + timeslot.id);
                    applicationData.publishInfo('timeslot', timeslot);
                };

            }
        ]
    ).controller('SessionController', //declares a controller to display the sessions available within a specific time slot
        [ //Variables are declared that can be used throughout this controller
            '$scope',
            'dataService',
            'applicationData',
            '$routeParams',
            '$location',
            function ($scope, dataService, applicationData, $routeParams, $location) {
                $scope.sessions = [];
                let getSessions = function (timeslot) {
                    dataService.getSessions(timeslot).then(
                        function (response) {
                            $scope.sessions = response.data;
                            $scope.sessionCount = response.rowCount;
                        },
                        function (err) {
                            $scope.status = 'Unable to load data ' + err;
                        }
                    );
                };

                if ($routeParams && $routeParams.timeslot) {
                    //console.log($routeParams.timeslot);
                    getSessions($routeParams.timeslot);
                    $scope.show = true;
                }


                var sessionInfo = $location.path().substr(1).split('/');
                if (sessionInfo.length === 2) {
                    $scope.selectedSession = {id: sessionInfo[1]};
                }

                $scope.selectedSession = {};

                $scope.selectSession = function ($event, session) {
                    $scope.selectedSession = session;
                    $location.path('schedule/' + $routeParams.day + '/' + $routeParams.timeslot + '/' + session.id);
                    applicationData.publishInfo('session', session);
                };

            }
        ]
    ).controller('SessionDetailController', //manages the presentations that are within a chosen session
        [ //Variables are declared that can be used throughout this controller
            '$scope',
            'dataService',
            'applicationData',
            '$routeParams',
            '$location',
            function ($scope, dataService, applicationData, $routeParams, $location) {
                $scope.details = [];
                let getDetails = function (session) {
                    dataService.getDetails(session).then(
                        function (response) {
                            $scope.details = response.data;
                            $scope.detailCount = response.rowCount;
                        },
                        function (err) {
                            $scope.status = 'Unable to load data ' + err;
                        }
                    );
                };

                //if the routeParams are correct then getDetails based on those parameters.
                if ($routeParams && $routeParams.session) {
                    //console.log($routeParams.session);
                    getDetails($routeParams.session);
                    $scope.show = true;
                }

                //opens up the editor so that fields can be changed.
                $scope.showEditDetail = function ($event, detail, editorID) {
                    if(sessionStorage.userService) {
                        var element = $event.currentTarget,
                            padding = 22,
                            posY = (element.offsetTop + element.clientTop + padding) - (element.scrollTop + element.clientTop),
                            detailEditorElement = document.getElementById(editorID);

                        //console.log(detail);
                        $scope.selectedDetail = detail;
                        $scope.editorVisible = true;

                        detailEditorElement.style.position = 'absolute';
                        detailEditorElement.style.top = posY + 'px';
                    }
                };

                //Close the session editor
                $scope.abandonEdit = function () {
                    $scope.editorVisible = false;
                    $scope.selectedDetail = null;
                    //console.log("Edit Closed");

                };

                //Save the entered details onto the form and calls dataservice updateDetail method to input the data into the database.
                $scope.saveDetails = function () {
                    if (sessionStorage.userService) {
                        var n,
                            scount = $scope.details.length,
                            currentDetail;
                        $scope.editorVisible = false;

                        dataService.updateDetail($scope.selectedDetail).then(
                            //there are some errors where the program will sometimes get stuck inside updateDetail and never come back into
                            //this function which means that anything beneath here doesn't run.
                            // To band-aid fix this I have forced a page refresh within the updateDetail method which isn't the best method however
                            // is the most convenient.
                            function (response) {
                                $scope.status = response.status;
                                if (response.status === 200) { // if we saved the file then update the screen
                                    for (n = 0; n < scount; n += 1) {
                                        currentDetail = $scope.details[n];
                                        if (currentDetail.sessionsID === $scope.selectedDetail.sessionsID) {
                                            $scope.details[n] = angular.copy($scope.selectedDetail);
                                            break;
                                        }
                                    }
                                }
                                //console.log(response);
                                // reset selectedDetail
                                $scope.selectedDetail = null;
                            },
                            function (err) {
                                $scope.status = "Error with save " + err;
                            }
                        );
                    }
                };

            }
        ]
    );
}());