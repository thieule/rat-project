'use strict';

/* User Controllers */
var ratAppUser = angular.module('ratApp.userModule', []);

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
        
     $scope.gridOptions = {
        data: 'employers',
        enablePinning: true,
        columnDefs: [{ field: "full_name", width: 120 , pinned: true },
                    { field: "code", width: 120},
                    { field: "birthday", width: 120 },
                    { field: "status", width: 120 },
                    { field: "personal_email" },
                    { field: "address",width:200,title:"Address" },
                    { field: "gender", width: 120 }],
         plugins: [new ngGridCsvExportPlugin()],
        // plugins: [new ngGridPdfExportPlugin()],
         showFooter: true
    };
    
        $scope.initData();
});
