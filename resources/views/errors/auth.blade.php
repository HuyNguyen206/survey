@extends('layouts.appError')

@section('content')
    <div class="page-content">
        <div class="title">You don't have permission.</div>
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