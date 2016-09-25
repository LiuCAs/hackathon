hackathonApp.config(['$routeProvider', function($routeProvider) {
    var originalWhen = $routeProvider.when;

    $routeProvider.when = function(path, route) {
        route.resolve = route.resolve || (route.resolve = {});
        return originalWhen.call($routeProvider, path, route);
    };
    $routeProvider
        .when('/', {
            templateUrl: 'main',
            controller: 'MainController'
        })
        .otherwise({
            redirectTo: '/'
        });
}]);
