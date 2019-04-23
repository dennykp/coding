<html>
<head>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.10/angular.min.js"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>


<div class="card">
  <div class="card-header">
    TES ANGULAR
  </div>
  <div class="card-body">
    <h5 class="card-title">DATA FORM</h5>
    <p class="card-text">
	<div ng-app="myApp" ng-controller="ctrl">
<table border="0"><tr><td>
1. Input:</td><td> <input type="text" class="form-control" ng-model="nama"></td><td>Result: {{nama}}</td></tr>
<tr><td>2. Angka:</td><td><input type="number" class="form-control" ng-model="number"></td><td>
<h3>Price = {{ number | currency : "IDR " : 3}}</h3></td></tr>
<tr><td>3.	Soal 3</td>
<td>
<ul>
<li ng-repeat='nm in karyawan'>{{nm}}</li>
</ul></td></tr>
<tr><td>
4. Soal 4:</td> <td><select class="form-control" ng-model="user">
<option ng-repeat='nama in user'>
{{nama}}
</option>
</td></tr>
</div>
</table>
	
	</p>
    
  </div>
</div>
<script>
var app = angular.module('myApp', []);
app.controller('ctrl', function($scope) {
    $scope.number = "";
	$scope.karyawan = ['Budi','Ainal','Ade','Michael','Burhan','Winny','Danu'];
	$scope.user = ['Budi','Ainal','Ade','Michael','Burhan','Winny','Danu'];
});

</script>
<b>oke siap</b>
yiha
</body>
</html>