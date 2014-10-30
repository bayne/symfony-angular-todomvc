/*global angular */

/**
 * Services that persists and retrieves TODOs from the Symfony2 backend
 */
angular.module('todomvc')
  .service('todoRepository', ['Restangular', function (Restangular) {
    'use strict';
    var TodoRepository = function() {
      var resource = Restangular.allUrl('todos');
      this.fetchAll = function() {
        return resource.getList();
      };
      this.create = function (todo) {
        return resource.post(todo);
      };
      this.persist = function(todo) {
        return resource.put(todo);
      }
    };
    return new TodoRepository();
  }])
  .factory('Todo', ['Restangular', function(Restangular) {
    var todoRepository = Restangular.all('todos');
    return todoRepository;
  }])
;
