@extends('layouts/master')
@section('page_class')user-profile @stop

@section('navigation') @parent @stop

@include('partial.message')

@section('content')
	@parent
	@section('panel_left_content')
		@include('user.partial.menu_dashboard')
	@stop
	@section('center_content')

		<div class="page-header">
			<h5>{{ trans('user.user_account_settings') }}</h5>
		</div>

		<form class="form-horizontal" role="form" method="POST" action="{{ route('user.update', ['user' => $user]) }}">

		{{ csrf_field() }}
		{{ method_field('PUT') }}

		<div ng-controller="ProfileController" ng-cloak>

			<div ng-show="disabled" class="alert alert-danger" role="alert">
				<small>{{ trans('user.account_disabled_description') }}</small>
			</div>

			<div class="row">
				<div class="col-lg-12">
						<tabset>
							{{-- user information --}}
							<tab heading="{{ trans('user.my_profile') }}" onclick="document.getElementById('referral').value = 'profile'">
								<div class="row">&nbsp;</div>
								<div class="row">
									<div class="col-lg-12">
										<div class="pull-left" ng-controller="uploadCtrl">
											<div class="progress ng-cloak" ng-show="progress">
											  <div class="progress-bar"  role="progressbar" aria-valuenow="[[progress]]" aria-valuemin="0" aria-valuemax="100" style="width: [[progress]]%;">
											    [[progress]]%
											  </div>
											</div>
											<div class="user-photo">
												<img src="[[picture]]" class="thumbnail" style="width:80px;"  alt="Photo" ng-file-select ng-model="files" ngf-accept="'image/*'" accept="image/*">
												<input type="hidden" value="[[file!=''?file:picture]]" name="pic_url">
											</div>
										</div>

										<h5>{{ ucwords($user->profile->fullName) }}</h5>

									</div>
								</div>
								<div class="row">
									<div class="col-lg-12">
										@include('user.partial.user_inputs')
									</div>
								</div>
							</tab>

							{{-- social information --}}
							<tab heading="{{ trans('user.social_information') }}" onclick="document.getElementById('referral').value = 'social'">
								<div class="row">&nbsp;</div>
								<div class="row">
									<div class="col-lg-12">
										@include('user.partial.optional_inputs')
									</div>
								</div>
							</tab>

							{{-- security information --}}
							<tab heading="{{ trans('user.pass_account') }}" onclick="document.getElementById('referral').value = 'account'">
								<div class="row">&nbsp;</div>
								<div class="row">
									<div class="col-lg-12">
										@include('user.partial.security_inputs')
									</div>
								</div>
							</tab>
						</tabset>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-12">
					<hr>
				</div>
			</div>
			<div class="row">
				{{-- Submit Button --}}
				<div class="col-lg-12">
					<button ng-hide="wantDelete" type="submit" class="btn btn-sm btn-success">
						<span class="glyphicon glyphicon-floppy-disk"></span>&nbsp;
						{{ trans('user.update_profile') }}
					</button>
				</div>
			</div>

		</div>

			<input type="hidden" name="referral" id="referral" value="profile">
		</form>
	@stop

@endsection
{{-- Pie de pagina --}}
@section('footer')
	@parent
@stop
{{-- Javascript --}}
@section('scripts')
	@parent
	{!! Html::script('/js/vendor/file-upload/angular-file-upload-shim.min.js') !!}
    {!! Html::script('/js/vendor/file-upload/angular-file-upload.min.js') !!}
    <script>
        (function(app){
        	//Control del profile de usuario
			app.controller('ProfileController',['$scope','$http','notify',function($scope, $http,notify){
				$scope.wantDelete = false;
				$scope.disabled = false;
				var disabled = '{{ $user->disabled_at }}';
				if (disabled !== '') $scope.disabled = new Date(disabled);

				$scope.disableAccount = function(){
					$http.patch("{{ route('user.action', ['action' => 'disable']) }}").
						success(function(data, status) {
							if (data.success) {
								$scope.wantDelete = false;
								$scope.disabled = new Date(data.date);
								notify({
									messageTemplate: '<p>' + data.message + '</p>',
									classes:'alert-danger'
								});
							}
						}).
						error(function(data, status, headers, config) { console.log('sdsd');
							notify({
								messageTemplate: '<p>' + data.message + '</p>',
								classes:'alert-error',
								duration:2000
							});
						});
				};

				$scope.enableAccount = function(){
					$http.patch("{{ route('user.action', ['action' => 'enable']) }}").
						success(function(data, status) {
							if (data.success) {
								$scope.wantDelete = false;
								$scope.disabled = false;
								notify({
									messageTemplate: '<p>' + data.message + '</p>',
									classes:'alert-success'
								});
							}
						}).
						error(function(data, status, headers, config) {
							notify({duration:2000,message:data.message,classes:'alert-error'});
						});
				};
			}]);

			//Upload
            app.controller('uploadCtrl', ['$scope', '$upload', '$timeout','notify', function ($scope, $upload, $timeout,notify) {
            	$scope.usingFlash = FileAPI && FileAPI.upload != null;
				$scope.fileReaderSupported = window.FileReader != null && (window.FileAPI == null || FileAPI.html5 != false);
				$scope.picture = '{{ $user->pic_url }}';

            	$scope.$watch('files', function () {
                    upload($scope.files);
                });
                $scope.file='';
                $scope.progress='';

                function upload(files) {
                    if (files && files.length) {
                        for (var i = 0; i < files.length; i++) {
                            var file = files[i];
                            var url="{{ route('user.update', ['user' => $user]) }}";
                            $upload.upload({
                                url: url,
                                fields: {"referral":"upload", "_token":'{{ csrf_token() }}', "_method":"PATCH"},
                                file: file
                            }).progress(function (evt) {

                                var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
                                $scope.progress= progressPercentage + '% ';

                            }).success(function (data, status, headers, config) {

                            		$scope.progress= '';
                                	notify({
                                		duration:2000,
                                		messageTemplate: "<p>{{ trans('responses.success') }}</p>",
                                		classes:'alert alert-danger'
                                	});
									generateThumb(file);

                            }).error(function(data, status, headers, config) {

                            	$scope.progress= '';
								notify({
                            		duration:4000,
                            		messageTemplate: "<p>{{ trans('responses.errors.avatar') }}</p>",
                            		classes:'alert alert-error'
                            	});

							});
                        }
                    }
                }


                function generateThumb(file) {
					if (file !== null) {
						if ($scope.fileReaderSupported && file.type.indexOf('image') > -1) {
							$timeout(function() {
								var fileReader = new FileReader();
								fileReader.readAsDataURL(file);
								fileReader.onload = function(e) {
									$timeout(function() {
										$scope.picture = file.dataUrl = e.target.result;
									});
								};
							});
						}
					}
				}
            }]);

        })(angular.module("AntVel"))
    </script>
@stop
@section('before.angular') ngModules.push('angularFileUpload'); @stop
