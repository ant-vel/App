@extends('emails.template')
@section('content')
<table width="100%" border="0" align="center" cellpadding="2" cellspacing="2" style="font-family:Verdana, Geneva, sans-serif; font-size:12px; text-align:left">
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td align="left">
            {{ trans('user.emails.email_confirmation.msg_01') }}
        </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td align="left">
            <a href="{{ $route }}">
                {{ $route }}
            </a>
        </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td align="left">
            {{ trans('user.emails.email_confirmation.msg_02') }}
        </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td align="left">
            {{ trans('user.emails.email_confirmation.msg_03') }}
                <a href="mailto:{{ $main_company['support_email'] }}">{{ $main_company['support_email'] }}</a>
            {{ trans('user.emails.email_confirmation.msg_04') }}
        </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
        <td align="left">
           {{ trans('user.emails.email_confirmation.msg_05') }}
        </td>
    </tr>
</table>
@endsection