/*global angular */

/**
 * The main controller for the app. The controller:
 * - retrieves and persists the model via Restangular
 * - exposes the model to the template and provides event handlers
 */
angular.module('todomvc')
  .controller('TodoCtrl', ['$scope', '$routeParams', '$filter', 'Restangular', '$q', function ($scope, $routeParams, $filter, Restangular, $q) {
    'use strict';

    $scope.todos = [];
    Restangular.all('todos').getList().then(function(todos) {
      $scope.todos = todos;
    });

    $scope.newTodo = {title: ''};
    $scope.editedTodo = null;

    // Monitor the current route for changes and adjust the filter accordingly.
    $scope.$on('$routeChangeSuccess', function () {
      var status = $scope.status = $routeParams.status || '';

      $scope.statusFilter = (status === 'active') ?
        { completed: false } : (status === 'completed') ?
        { completed: true } : null;
    });

    $scope.$watch('todos', function (newValue, oldValue) {
      $scope.remainingCount = $filter('filter')($scope.todos, { completed: false }).length;
      $scope.completedCount = $scope.todos.length - $scope.remainingCount;
      $scope.allChecked = !$scope.remainingCount;
    }, true);

    $scope.completeTodo = function (todo) {
        return todo.put();
    };

    $scope.addTodo = function () {
      $scope.todos.post($scope.newTodo).then(function(todo) {
        $scope.todos.push(todo);
      });
      $scope.newTodo = {title: ''};
    };

    $scope.editTodo = function (todo) {
      $scope.editedTodo = todo;
      // Clone the original todo to restore it on demand.
      $scope.originalTodo = angular.extend({}, todo);
    };

    $scope.doneEditing = function (todo, $index) {
      $scope.editedTodo = null;
      todo.title = todo.title.trim();

      if (!todo.title) {
        $scope.removeTodo(todo, $index);
      } else {
        todo.put();
      }
    };

    $scope.revertEditing = function (todo, $index) {
      $scope.todos[$index] = $scope.originalTodo;
      $scope.doneEditing($scope.originalTodo, $index);
    };

    $scope.removeTodo = function (todo, $index) {
      $scope.todos.splice($index, 1);
      todo.remove();
    };

    $scope.clearCompletedTodos = function () {
      var completedTodos = $scope.todos.filter(function (val) {
        return val.completed;
      });
      var promises = _.map(completedTodos, function(completedTodo) {
        return completedTodo.remove();
      });
      $q.all(promises).then(function() {
        return $scope.todos.getList();
      }).then(function(todos) {
        $scope.todos = todos;
      });
    };

    $scope.markAll = function (completed) {
      $scope.todos.forEach(function (todo) {
        todo.completed = !completed;
        $scope.completeTodo(todo);
      });
    };
  }]);
