/*global angular */

/**
 * Directive that executes an expression when the element it is applied to gets
 * an `escape` keydown event.
 */
angular.module('todomvc')
  .directive('todoEscape', function () {
    'use strict';

    var ESCAPE_KEY = 27;

    return function (scope, elem, attrs) {
      elem.bind('keydown', function (event) {
        if (event.keyCode === ESCAPE_KEY) {
          scope.$apply(attrs.todoEscape);
        }
      });
    };
  })
/**
 *
 * Directive that places focus on the element it is applied to when the
 * expression it binds to evaluates to true
 */
  .directive('todoFocus', function todoFocus($timeout) {
    'use strict';

    return function (scope, elem, attrs) {
      scope.$watch(attrs.todoFocus, function (newVal) {
        if (newVal) {
          $timeout(function () {
            elem[0].focus();
          }, 0, false);
        }
      });
    };
  });