@extends('layouts.app')

@section('content')
    @include('surveys.customerInfoSurvey')
    @include('surveys.customerHistorySurvey')
    @include('surveys.surveyError')
    @include('surveys.surveyCreateDashboard')
    @include('surveys.checklist')
    @include('surveys.preChecklist')
    @include('surveys.fowardDepartment')
@endsection