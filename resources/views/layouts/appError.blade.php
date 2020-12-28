<html lang="en-US" ng-app="outbound">
    <!--<meta name="csrf-token" content="{{ csrf_token() }}" />-->
    <head>
        <title>SurveysHiFPT</title>
        <!-- Load Bootstrap CSS -->

        <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
              rel="stylesheet">
        <link rel="stylesheet" href="{{asset('outboundapp/css/jquery-ui.min.css')}}" />
        <link rel="stylesheet" type="text/css"
              href="{{asset('outboundapp/bootstrap/css/bootstrap.min.css')}}" />
        <link rel="stylesheet" type="text/css"
              href="{{asset('outboundapp/font-awesome/css/font-awesome.min.css')}}" />

        <link rel="stylesheet" type="text/css"
              href="{{asset('outboundapp/css/tooltipster.css')}}" />
        <link rel="stylesheet" type="text/css"
              href="{{asset('outboundapp/css/angular-material.min.css')}}" />

        <!-- ace styles -->
        <link rel="stylesheet" href="{{asset('outboundapp/css/chosen.min.css')}}" />
        <link rel="stylesheet" href="{{asset('outboundapp/css/font-awesome.min.css')}}" />

 <!--<script src="{{asset('assets/js/jquery-2.0.3.min.js')}}"></script>-->
         <!--<script src='assets/js/chosen.jquery.min.js'></script>-->

        <!--<link rel="stylesheet" href="{{asset('assets/css/ace.min.css')}}" />-->
        <link rel="stylesheet" href="{{asset('outboundapp/css/ace-rtl.min.css')}}" />
        <link rel="stylesheet" href="{{asset('outboundapp/css/ace-skins.min.css')}}" />
        <link rel="stylesheet" href="{{asset('outboundapp/css/ace.min.css')}}" />
        <link rel="stylesheet" href="{{asset('outboundapp/css/custom-chosen-content.css')}}" />
        <link rel="stylesheet" type="text/css"
              href="{{asset('outboundapp/css/select.css')}}" />

        <link rel="stylesheet" type="text/css"
              href="{{asset('outboundapp/css/styleHiFPT.css')}}" /> 
        <link rel="stylesheet" type="text/css"
              href="{{asset('outboundapp/css/font_face.css')}}" />
        <link rel="stylesheet" type="text/css"
              href="{{asset('outboundapp/css/angular-moment-picker.min.css')}}" />
        <link rel="stylesheet" type="text/css"
              href="{{asset('outboundapp/css/stylesHiFPT.css')}}" />

    </head>
    <body  style="background-color: #fff" >
        <div class="navbar navbar-default" id="navbar" style="height: 1px;">
            <script type="text/javascript">
                try {
                ace.settings.check('navbar', 'fixed')
                } catch (e) {
                }
            </script>

            <div class="navbar-container" id="navbar-container">
                <div class="navbar-header pull-left">
                    <a href="<?php echo url('/'); ?>" class="navbar-brand no-padding" style=" margin-top: 3px;
                       display: table;
                       height: 0px;">
                        <img src="{{asset('outboundapp/img/icon_cv.png')}}" height="40" style="display: table-cell;
                             vertical-align: middle;"/>
                        <small style="display: table-cell;
                               vertical-align: middle;">
                            Tool Survey
                        </small>
                    </a><!-- /.brand -->
                </div><!-- /.navbar-header -->

                <div class="navbar-header pull-right" role="navigation">
                    <ul class="nav ace-nav">
                        <li class="light-blue">
                            <a data-toggle="dropdown" href="javascript:void(0)" class="dropdown-toggle">
                                <img class="nav-user-photo" src="{{asset('outboundapp/img/user.jpg')}}" alt="Jason's Photo" />
                                <span class="user-info">
                                    <small>{{ trans('nav-sidebar.Welcome')}},</small>
                                    {{Auth::user()->name}}
                                </span>
                                <i class="icon-caret-down"></i>
                            </a>
                            <ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                                <li>
                                    <a href="{{ url('/logout')}}">
                                        <i class="fa fa-sign-out"></i>
                                        {{ trans('nav-sidebar.Logout')}}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul><!-- /.ace-nav -->
                </div><!-- /.navbar-header -->
            </div><!-- /.container -->
        </div>
        <form>           
            {!! csrf_field() !!}                                          
                        <div class="box-check-multi" style="width: 100%;">
                            <div class="table-form" id="cnt1">

                                <div class="surveycontent" id="surveycontentID" style="padding: 14px; color: red">

                                    @yield('content')
                                </div>
                            </div>
                        </div>
                        
                        
        </form>

        <!-- Satisfaction Survey - END -->
        <!--<div ui-view></div>-->

        <!-- Modal -->

    </div>
    <script src="{{asset('outboundapp/js/jquery.min.js')}}"></script>
    <script src="{{asset('outboundapp/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('outboundapp/js/jquery.tooltipster.js')}}"></script>
    <script src="{{asset('outboundapp/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('outboundapp/js/jquery.dataTables.bootstrap.js')}}"></script>
    <!-- Load Javascript Libraries (AngularJS, JQuery, Bootstrap) -->
    <!--<script type="text/javascript" src="{{asset('//code.jquery.com/ui/1.11.4/jquery-ui.js')}}"></script>-->
    <script src="{{asset('outboundapp/js/jquery-ui-1.10.3.full.min.js')}}"></script>
    <!--<script src="{{asset('outboundapp/lib/angular1.4.8/angular.min.js')}}"></script>-->
    <!--<script src="{{asset('outboundapp/lib/multiselect.js')}}"></script>-->
    <!--<script src="{{asset('outboundapp/js/moment-with-locales.js')}}"></script>-->
    <!-- <script src="assets/outboundapp/controllers/employees"></script> -->
 

    <style>
        .datepickerdemoBasicUsage {
            /** Demo styles for mdCalendar. */ }
        .datepickerdemoBasicUsage md-content {
            padding-bottom: 200px; }
        .datepickerdemoBasicUsage .validation-messages {
            font-size: 12px;
            color: #dd2c00;
            margin: 10px 0 0 25px; }
        .survey-question
        {
            width: 20px  !important; 
            height:   20px !important;
            cursor: pointer;
        }
        textarea
        {
            background-color: white;    
        }
        .radio-survey
        {
            padding-bottom: 16px;
        }
        .verticle-radio
        {
            position: relative;
            bottom: 7px;
        }
    </style>
</div>
</body>
</html>



