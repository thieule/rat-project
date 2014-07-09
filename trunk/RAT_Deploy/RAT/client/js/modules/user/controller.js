'use strict';

/* User Controllers */
var ratAppUser = angular.module('ratApp.userModule', []);

ratApp.controller('userController',

        function($scope,$http,$location,userSvc) {
        
        // Init scope
        $scope.indexPath = '/2dea96fec20593566ab75692c9949596833adc';
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
                    rat.global.baseAddress+$scope.indexPath +'/logout',
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
                rat.global.baseAddress+$scope.indexPath+'/login',
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
                    rat.global.baseAddress+$scope.indexPath+'/list'
                    
            ).then(
                function (response) {
                    $scope.loading = false;
                    $scope.employers = response.data.data.list.data;

                }
            );

        };
        
        $scope.userexportxlsx = function () {
            window.location.href =rat.global.baseAddress+$scope.indexPath +'/exportxslx';
           

        };
        
//      $scope.initData();
        
      $scope.filterOptions = {
        filterText: "",
        useExternalFilter: true
    }; 
    $scope.totalServerItems = 0;
    $scope.pagingOptions = {
        pageSizes: [100,250, 500, 1000],
        pageSize: 100,
        currentPage: 1
    };	
      
       $scope.setPagingData = function(data, page, pageSize){	
        var pagedData = data.slice((page - 1) * pageSize, page * pageSize);
        $scope.myData = pagedData;
        $scope.totalServerItems = data.length;
        if (!$scope.$$phase) {
            $scope.$apply();
        }
    };
    $scope.getPagedDataAsync = function (pageSize, page, searchText) {
        setTimeout(function () {
            $scope.loading = true;
            var data;
            if (searchText) {
                var ft = searchText.toLowerCase();
                $http.get(rat.global.baseAddress+$scope.indexPath +'/list').success(function (largeLoad) {
                    $scope.loading = false;
                    data = largeLoad.data.list.data.filter(function(item) {
                        return JSON.stringify(item).toLowerCase().indexOf(ft) != -1;
                    });
                    $scope.setPagingData(data,page,pageSize);
                });            
            } else {
                $http.get(rat.global.baseAddress+$scope.indexPath +'/list').success(function (largeLoad) {
                    $scope.loading = false;
                    $scope.setPagingData(largeLoad.data.list.data,page,pageSize);
                  
                });
            }
        }, 100);
    };
    
    $scope.getPagedDataAsync($scope.pagingOptions.pageSize, $scope.pagingOptions.currentPage);
	
    $scope.$watch('pagingOptions', function (newVal, oldVal) {
        if (newVal !== oldVal && newVal.currentPage !== oldVal.currentPage) {
          $scope.getPagedDataAsync($scope.pagingOptions.pageSize, $scope.pagingOptions.currentPage, $scope.filterOptions.filterText);
        }
    }, true);
    $scope.$watch('filterOptions', function (newVal, oldVal) {
        if (newVal !== oldVal) {
          $scope.getPagedDataAsync($scope.pagingOptions.pageSize, $scope.pagingOptions.currentPage, $scope.filterOptions.filterText);
        }
    }, true);
	
        
     $scope.gridOptions = {
        data: 'myData',
        enablePinning: true,
        columnDefs: [{ field: "NewCode", width: 120 , pinned: true },
                    { field: "VietnameseName", width: 160 , pinned: true },
                    { field: "Position", width: 170 },
                    { field: "Skill", width: 160 },
                    { field: "CurrentProject" , width: 160 },
                    { field: "English",width:200,title:"English" },
                    { field: "Experience", width: 120 }],
//         plugins: [new ngGridCsvExportPlugin()],
        // plugins: [new ngGridPdfExportPlugin()],
        showFooter: true,
        enablePaging: true,
        showGroupPanel: true,
        totalServerItems: 'totalServerItems',
        pagingOptions: $scope.pagingOptions,
        filterOptions: $scope.filterOptions,
        showFilter: true,
        enableColumnResize: true,
        enableColumnReordering: true,
        showColumnMenu: true,
    };
    
       
});
