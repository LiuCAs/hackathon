hackathonApp.controller('MainController', ['$scope', '$rootScope', '$', 'NgMap', '$http', '$uibModal',
    function($scope, $rootScope, $, NgMap, $http, $uibModal) {

    if ('geolocation' in navigator) {
        $rootScope.geolocation = true;
        navigator.geolocation.getCurrentPosition(function(position) {
            $rootScope.actualCoords = [position.coords.latitude, position.coords.longitude];
        }, function(err) {
            console.log(err);
            $rootScope.actualCoords = [52.4064,16.9252];
        });
    } else {
        $rootScope.geolocation = false;
    }

    $rootScope.mapZoom = 12;
    $rootScope.rightBar = false;
    $scope.showBackButton = false;
    $scope.icon ={
        "path": "CIRCLE",
        "scale": 8,
        "strokeColor": "#F00",
        "fillColor": "#F00"
    };

    NgMap.getMap().then(function(map) {
        map.getCenter();
    });

    $scope.showDescription = function(k, v) {
        $rootScope.markerDetail = [$rootScope.marker[v]];
        if ($rootScope.markerDetail) {
            $rootScope.actualCoords = [$rootScope.markerDetail[0].lat, $rootScope.markerDetail[0].lng];
            $rootScope.mapZoom = 16;
            $rootScope.rightBar = true;
            $scope.showBackButton = true;
        }
    };

    $scope.showDescriptionFromList = function(k, v) {
        if ($rootScope.markerDetail.length > 1) {
            $rootScope.markerDetail = [$rootScope.marker[v]];
            if ($rootScope.markerDetail) {
                $rootScope.actualCoords = [$rootScope.markerDetail[0].lat, $rootScope.markerDetail[0].lng];
                $rootScope.mapZoom = 16;
                $rootScope.rightBar = true;
                $scope.showBackButton = true;
            }
        }
    };

    $scope.showAllMarker = function() {
        $scope.showBackButton = false;
        $rootScope.mapZoom = 11;
        $rootScope.markerDetail = $rootScope.marker;
        if ($rootScope.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                $rootScope.actualCoords = [position.coords.latitude, position.coords.longitude];
            }, function(err) {
                console.log(err);
                $rootScope.actualCoords = [52.4064,16.9252];
            });
        }
    };

    $scope.showModal = function(details) {
        $rootScope.modalRedirect = $uibModal.open({
            templateUrl: 'modal',
            controller: 'ModalController',
            size: 'lg modal',
            resolve: {
                response: function() {
                    return details;
                }
            }
        });
    };


    $(window).on("load",function(){
        $(".right-bar").mCustomScrollbar();
    });
}]);
