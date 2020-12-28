@extends('layouts.test')

@section('content')
<!--<div ng-app="testApp" >
	<div ng-controller="myCtrl">
		First Name: <input type="text" ng-model="firstName"><br/>
		Last Name: <input type="text" ng-model="lastName" ng-click="changeName()"><br/>
		<br/>
		<h1>Full Name: <i ng-bind="firstName"></i> <i ng-bind="lastName"></i></h1>
		<div w3-test-directive></div>
		
	</div>
</div>

<script>
	var app = angular.module('testApp', []);
	app.controller('myCtrl', function($scope) {
		$scope.changeName = function() {
			$scope.firstname = "Hoang";
		},
		$scope.lastName= "Mã";
	});
	app.directive("w3TestDirective", function() {
    return {
		restrict : "A",
        template : "<h1>Made by a directive!</h1>"
    };
});
</script>-->

<table >
	<tr>
		<td style="border: 1px #000 solid;" rowspan="4">abc</td>
		<td style="border: 1px #000 solid;">abc</td>
	</tr>
	<tr>
		<td style="border: 1px #000 solid;">abc</td>
	</tr>
	<tr>
		<td style="border: 1px #000 solid;">abc</td>
	</tr>
	<tr>
		<td style="border: 1px #000 solid;">abc</td>
	</tr>
</table>

@stop

