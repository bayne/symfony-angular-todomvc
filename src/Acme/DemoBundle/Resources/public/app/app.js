/*global angular */

/**
 * The main TodoMVC app module
 *
 * @type {angular.Module}
 */
angular.module('todomvc', ['ngRoute', 'restangular'])
  .config(function ($routeProvider) {
    'use strict';

    $routeProvider.when('/', {
      controller: 'TodoCtrl',
      templateUrl: 'todomvc-index.html'
    }).when('/:status', {
      controller: 'TodoCtrl',
      templateUrl: 'todomvc-index.html'
    }).otherwise({
      redirectTo: '/'
    });
  })
  .config(function($interpolateProvider) {
        $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  })
  .config(['RestangularProvider', function (RestangularProvider) {
    RestangularProvider.setBaseUrl('/');
    RestangularProvider.setResponseExtractor(function(response, operation, what, url) {
      if (operation == 'getList') {
        return _.toArray(response[what]);
      } else {
        return response;
      }
    });
    RestangularProvider.addRequestInterceptor(function (element, operation, what, url) {
      var newRequest = {};
      if (operation == 'post' || operation == 'put') {
        what = what.split('');
        what.pop();
        what = what.join('');
      }
      if (operation == 'put') {
        delete element._links;
      }
      newRequest[what] = element;
      return newRequest;
    });
    RestangularProvider.setRestangularFields({
      selfLink: '_links.self.href'
    });
    RestangularProvider.setDefaultRequestParams('get', {limit: 100});
  }])
;
