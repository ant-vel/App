<?php

/**
 * Contains the notify plugin style
 *
 * @var string
 */
$class = Session::get('messageClass') ? Session::get('messageClass') : 'alert alert-success';
$class = is_array($class) ? implode('', $class) : $class;

/**
 * Contains the notify plugin message.
 *
 * @var string / array
 */
$m = Session::get('status') ?: Session::get('message');
$m = $m ? (is_array($m) ? Html::ul($m) : '<p>'.$m.'</p>') : '';
$m = $m ? (is_array($m) ? Html::ul($m) : '<p>'.$m.'</p>') : '';

/**
 * Laravel errors message controller
 *
 * @var array
 */
$e = $errors->any() ? Html::ul($errors->all()) : '';

//forgetting flash data to control app notify messages.
Session::forget('message');
Session::forget('messageClass');
Session::save();

?>
@if($m!='' || $e!='')
    <div ng-controller="MessageController"></div>
    @section('scripts')
    @parent
        <script>
            (function(app){
                app.controller('MessageController',['notify',function(notify) {
                  @if($m!='')
                    notify({duration:5000,messageTemplate:'{!! $m !!}',classes:'{{$class}}'});
                  @endif
                  @if($e!='')
                    notify({duration:5000,messageTemplate:'{!! $e !!}',classes:'alert alert-danger'});
                  @endif
                }]);
            })(angular.module("AntVel"));
        </script>
    @stop
@endif
