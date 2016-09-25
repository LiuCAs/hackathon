angular.module('jquery', [])
    .factory('$', ['$window', function ($window) {
        return $window.$;
    }]);

var hackathonApp = angular.module('hackathonApp', ['ngRoute', 'ngAnimate', 'jquery', 'ui.bootstrap', 'ngMap']);

