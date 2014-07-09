'use strict';


// Declare app level module which depends on filters, and services
var ratApp = angular.module('ratApp', [
	'ngRoute',
        'ngGrid',
        'gantt'
]);
ratApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider.when('/login', {templateUrl: 'partials/login.html',controller: 'userController'});
	$routeProvider.when('/users', {templateUrl: 'partials/users.html',controller: 'userController'});
        $routeProvider.when('/employer/book', {templateUrl: 'partials/project_employer_book.html',controller: 'employerController'});
        $routeProvider.when('/admin/dropdownlable', {templateUrl: 'partials/admin/dropdownlable.html',controller: 'DropdownLableAdminController'});
	$routeProvider.otherwise({redirectTo: '/users'});
}]);



