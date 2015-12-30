angular.module('schedulerApp', ['ngResource'])

.factory('Task', ['$resource',
  function($resource){
    return $resource('task.php', {}, {
      get: {url:'/TaskScheduler/rest/task.php', method:'GET', isArray:true},
      read: {url:'/TaskScheduler/rest/task.php?id=:id', method:'GET', isArray:false},
      add: {url:'/TaskScheduler/rest/task.php', method:'POST', isArray:false},
      delete: {url:'/TaskScheduler/rest/task.php?id=:id', method:'DELETE'}
    });
  }])

.controller('schedulerCtrl', ['$scope', 'Task', '$filter',
  function($scope, Task, $filter) {
    $scope.intervals = ['+1 week','+1 month','+1 year'];
    $scope.tasks = Task.get();
    $scope.task = {
      name:'name', 
      created:new Date(), 
      interval:$scope.intervals[0], 
      description:'about the task'};
    
    $scope.addTask = function(){
      //for now, rely on html5 client validation
      Task.add($scope.task, function(){
        $scope.tasks = Task.get();
      });
    };
    
    $scope.deleteTask = function(id){
      Task.delete({id:id}, function(){
        $scope.tasks = Task.get();
      });
    };
  }]);


