@extends('layouts/master')

@section('page_class') wrapper-page @stop

@section('navigation')
	&nbsp;
@stop

@include('partial.message')

@section('content')

	<div class="content_wrapper_header">
		<h3>
			<a href="/" title="{{ trans('globals.go_back_label') }}">
				{{ trans('user.reset_password') }}
			</a>
		</h3>
	</div>

	<div class="content_wrapper">
		<div class="row">
			<div class="col-md-12">

				@if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

				<form role="form" method="POST" action="{{ url('/password/email') }}">

					<input type="hidden" name="_token" value="{{ csrf_token() }}">

					<div class="form-group">
						<label>{{ trans('user.email') }}:</label>
						<div class="input-group">
							<div class="input-group-addon"><span class="fa fa-envelope-o"></span></div>
							<input type="email" class="form-control" name="email" value="{{ old('email') }}">
						</div>
					</div>

					<div class="form-group">

						<hr>

						<button type="submit" class="btn btn-primary">
							<span class="glyphicon glyphicon-ok-circle"></span>&nbsp;
							{{ trans('user.reset_password') }}
						</button>

						<a href="{{ route('login') }}" class="btn btn-default">
							<span class="glyphicon glyphicon-log-in"></span>&nbsp;
							{{ trans('user.sign_in_my_account') }}
						</a>
					</div>

				</form>

			</div>
		</div>
	</div>

@endsection

@section('footer')
	&nbsp;
@endsection
