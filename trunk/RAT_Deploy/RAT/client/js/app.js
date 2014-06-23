'use strict';


// Declare app level module which depends on filters, and services
var ratApp = angular.module('ratApp', [
	'ngRoute',
	'ratApp.userModule'
]);
ratApp.config(['$routeProvider', function($routeProvider) {
	$routeProvider.when('/login', {templateUrl: 'partials/login.html',controller: 'userController'});
	$routeProvider.when('/users', {templateUrl: 'partials/users.html',controller: 'userController'});
	$routeProvider.otherwise({redirectTo: '/users'});
}]);



