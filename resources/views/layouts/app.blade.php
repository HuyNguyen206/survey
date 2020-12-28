<html lang="en">
    <head>
        <title>SurveyOPENNET</title>
        <!-- Load Bootstrap CSS -->

        <meta name="description" content="overview &amp; stats" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <!-- basic styles -->
        <link rel="stylesheet" href="{{asset('assets/css/jquery-ui.min.css')}}" />
        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" />
        <link rel="stylesheet" href="{{asset('assets/css/font-awesome.min.css')}}" />

        <!-- ace styles -->

        <link rel="stylesheet" href="{{asset('assets/css/ace.min.css')}}" />
        <link rel="stylesheet" href="{{asset('assets/css/ace-rtl.min.css')}}" />
        <link rel="stylesheet" href="{{asset('assets/css/ace-skins.min.css')}}" />

        <link rel="stylesheet" href="{{asset('assets/css/custom-chosen-content.css')}}" />
        <link rel="stylesheet" href="{{asset('assets/css/style.css')}}" />
        <link rel="stylesheet" type="text/css"
              href="{{asset('assets/css/styles.css')}}" /> 
        <link rel="stylesheet" type="text/css"
              href="{{asset('assets/css/font_face.css')}}" />

        <!-- inline styles related to this page -->

        <!-- ace settings handler -->

        <script type="text/javascript">
            window.jQuery || document.write("<script src='{{asset('assets/js/jquery-2.0.3.min.js')}}'>" + "<" + "/script>");
        </script>


        <!-- <![endif]-->

        <script type="text/javascript">
            if ("ontouchend" in document)
                document.write("<script src='{{asset('assets/js/jquery.mobile.custom.min.js')}}'>" + "<" + "/script>");
        </script>
        <script src="{{asset('assets/js/ace-extra.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.redirect.js')}}"></script>
    </head>
    <body style="background-color: #fff" >
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
                        <img src="{{asset('assets/img/icon_cv.png')}}" height="40" style="display: table-cell;
                             vertical-align: middle;"/>
                        <small style="display: table-cell;
                               vertical-align: middle;">
                            @lang('common.toolSurvey')
                        </small>
                    </a><!-- /.brand -->
                </div><!-- /.navbar-header -->

                <div class="navbar-header pull-right" role="navigation">
                    <ul class="nav ace-nav">
                        <li class="light-blue">
                            <a style="padding: 10px;{{(App::getLocale() == 'vi') ? 'background-color: powderblue;' : ''}}" href="{{url('/lang/vi')}}"><img class="emo-resize" style="width: 20px;" src="{{asset("assets/img/viFlag.png")}}"></a>
                        </li>
                        <li class="light-blue">
                            <a style="padding: 10px;{{(App::getLocale() == 'en') ? 'background-color: powderblue;' : ''}}" href="{{url('/lang/en')}}"><img class="emo-resize" style="width: 20px;" src="{{asset("assets/img/enFlag.png")}}"></a>
                        </li>
                        <li class="light-blue">
                            <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                                <img class="nav-user-photo" src="{{asset('assets/img/user.jpg')}}" alt="Jason's Photo" />
                                <span class="user-info">
                                    <small>{{ trans('nav-sidebar.Welcome')}},</small>
                                    {{Auth::user()->name}}
                                </span>
                                <i class="icon-caret-down"></i>
                            </a>
                            
                            <ul class="user-menu pull-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">
                                <li>
                                    <a href="{{ url('/')}}">
                                        <i class="fa fa-sign-out"></i>
                                        {{ trans('nav-sidebar.History')}}
                                    </a>
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

        @yield('content')

        <script src="{{asset('assets/js/bootstrap.min.js')}}"></script>
        <script src="{{asset('assets/js/typeahead-bs2.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery-ui-1.10.3.full.min.js')}}"></script>
        <!-- ace scripts -->

        <script src="{{asset('assets/js/ace-elements.min.js')}}"></script>
        <script src="{{asset('assets/js/ace.min.js')}}"></script>
        
        <script type="text/javascript">
            $("#ace-settings-btn").hide();
        </script>
    </body>
</html>



