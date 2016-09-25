hackathonApp.controller('ModalController',
    ['$scope', '$rootScope', '$uibModal', '$uibModalInstance', 'response', '$http',
        function($scope, $rootScope, $uibModal, $uibModalInstance, response, $http) {
            $scope.response = response.oferta;

            $scope.ok = function() {
                $uibModalInstance.close();
            };

            $scope.cancel = function() {
                $uibModalInstance.dismiss('cancel');
            };

            $scope.close = function() {
                $uibModalInstance.close();
            };

            $rootScope.modalRedirect.result.then(function() {
            }, function() {});
        }
    ]);
hackathonApp.filter('unsafe', ['$sce', function($sce) { return $sce.trustAsHtml; }]);
