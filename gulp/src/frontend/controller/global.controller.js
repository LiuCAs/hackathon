hackathonApp.controller('GlobalController', ['$scope', '$rootScope', function($scope, $rootScope) {
    $rootScope.hideFilters = false;

    $scope.showFilters = function() {
        $rootScope.hideFilters = false;
        $rootScope.rightBar = false;
    };
}]);