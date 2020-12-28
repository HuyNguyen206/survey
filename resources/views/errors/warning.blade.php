@extends('layouts.app')

@section('content')
<?php $transFile = 'history'; ?>
<div class="page-content">
    <div style="color: black; font-size: 55px" >{{trans('warning.Warning')}}</div>
    <div style="color: black; font-size: 25px" >{{isset($message) ? $message : null}}</div>

    <div style="text-align: center">
        <button type="button" class="btn btn-primary" ><a style="color: white" href="{{url('/')}}">{{trans($transFile.'.BackToSurveyHistory')}}</a></button>
        <button type="button" class="btn btn-success" ><a style="color: white" href="{{$link}}">{{trans($transFile.'.GoToSurveyResult')}}</a></button>
    </div>
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