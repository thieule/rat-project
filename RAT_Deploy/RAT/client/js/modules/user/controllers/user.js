'use strict';

/* User Controllers */
var ratAppUser = angular.module('ratApp.userModule', []);

//   Authentication service
ratApp.service('userSvc',
    function() {
        // The service
        var service = {

            // Status of current session
            status: function() {
                return status;
            },

            // Reset session
            logout: function() {
                rat.userStorage.remove();
                rat.tokenStorage.remove();

            },


            // Start new session / Login user
            instance: function(token,user) {
                rat.tokenStorage.set(token);
                rat.userStorage.set(JSON.stringify(user));
            },

            // check login status
            isLogin: function() {
                var token = rat.tokenStorage.get();
                var user = rat.userStorage.get();
                if(!user) return false;

                user = JSON.parse(user);
                if(token && user.id) return true;
                return false;
            },
            // check login status
            current: function() {

                var user = rat.userStorage.get();
                if(!user) return null;

                user = JSON.parse(user);
                if(user.id) return user;
                return null;
            }


        };

        return service;
    });


ratApp.controller('userController',

        function($scope,$http,$location,userSvc) {
    // Init scope

        $scope.indexPath = '/index/#';
        $scope.user   = userSvc.current();
        $scope.employers = [];
        $scope.initData  = function () {
            if ( !userSvc.isLogin()) {
                // no logged user, we should be going to #login
                // not going to #login, we should redirect now

                $location.path( "/login" );

                return false;

            }else
            {
                if($location.path() == '/login')  $location.path( "/" );
            }

            $scope.userlist();
        };


            $scope.logout = function () {
                $scope.loading = true;
                var promise = $http.post(
                        rat.global.baseAddress+'/logout',
                    jQuery('#loginForm').serialize(),
                    {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
                ).then(
                    function (response) {
                             userSvc.logout();
                            $location.path( "/login");

                    }
                );
            };

        $scope.login = function () {
            $scope.loading = true;
            var promise = $http.post(
                rat.global.baseAddress+'/login',
                jQuery('#loginForm').serialize(),
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            ).then(
                function (response) {
                    $scope.loading = false;

                    if(response.data.data.login.status ==1){
                        userSvc.instance(response.data.token,response.data.data.login.user)
                        $scope.user = response.data.data.login.user;
                        $location.path( "/users");
                    }else{
                        $location.path( "/");
                    }
                }
            );
        };

        $scope.userlist = function () {
            $scope.loading = true;
            var promise = $http.get(
                    rat.global.baseAddress+'/list'
            ).then(
                function (response) {
                    $scope.loading = false;
                    $scope.employers = response.data.data.list.data;

                }
            );

        };
        $scope.initData();
});
