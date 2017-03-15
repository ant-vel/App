<div class="form-group">
	<div class="col-md-3 col-lg-3">
		{!! Form::label('old_password',trans('user.old_password')) !!}:
		<div class="input-group">
      		<div class="input-group-addon input-sm"><span class="fa fa-lock"></span></div>
			{!! Form::password('old_password', ['class'=>'form-control input-sm']) !!}
		</div>
	</div>
</div>

<div class="form-group">
	<div class="col-md-3 col-lg-3">
		{!! Form::label('password',trans('user.password')) !!}:
		<div class="input-group">
	      	<div class="input-group-addon input-sm"><span class="fa fa-lock"></span></div>
			{!! Form::password('password', ['class'=>'form-control input-sm']) !!}
		</div>
	</div>

	<div class="col-md-3 col-lg-3">
		{!! Form::label('password_confirmation',trans('user.password_confirmation')) !!}:
		<div class="input-group">
	      	<div class="input-group-addon input-sm"><span class="fa fa-lock"></span></div>
			{!! Form::password('password_confirmation', ['class'=>'form-control input-sm']) !!}
		</div>
	</div>
</div>

<div ng-hide="disabled">
	<label>{{ trans('user.disable_account') }}:</label>
	<div class="panel panel-danger">
		<div class="panel-body">
		    <p class="text-warning">{{ trans('user.disable_account_description') }}</p>
		    <button type="button" ng-click="wantDelete = true" class="btn btn-xs btn-danger">{{ trans('user.disable_account') }}</button>
		    <div ng-show="wantDelete">
		    	<strong>{{ trans('user.are_you_sure') }}?</strong>
				<button type="button" class="btn btn-warning" ng-click="disableAccount()">{{ trans('globals.yes') }}</button>
				<button type="button" class="btn btn-success" ng-click="wantDelete = false">{{ trans('globals.no') }}</button>
		    </div>
		</div>
	</div>
</div>

<div ng-show="disabled">
	<div class="panel panel-danger">
		<label>{{ trans('user.enable_account') }}:</label>
		<div class="panel-body">
		    <p>{{ trans('user.enable_account_description') }}</p>
		    <button type="button" ng-click="enableAccount()" class="btn btn-xs btn-primary">{{ trans('user.enable_account') }}</button>
		</div>
	</div>
</div>
