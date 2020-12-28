@extends('layouts.app')

@section('content')
<div class="page-content">
    <div style="    color: black; font-size: 55px" >{{trans('error.AnErrorOccurred')}}</div>
    <div style="    color: black; font-size: 25px" >{{isset($message) ? $message : null}}</div>
</div>
<style>
    .page-content {
        height: 100%;
        text-align: center;
        display: inline-block;
        margin: 0;
        padding: 0;
        width: 100%;
        color: #B0BEC5;
        display: table;
        font-weight: 100;
        font-family: 'Lato';
    }

    .title {
        font-size: 72px;
        margin-bottom: 40px;
    }
</style>
@stop