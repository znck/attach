<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('pageDescription', $siteDescription)">

    <title>@yield('pageTitle') {{$siteName}} - Rahul Kadyan</title>

    <link rel="stylesheet" href="http://znck.me/assets/css/app.css">
</head>

<body>
<nav class="navbar navbar-light border-bottom">
    <div class="container">
        <a class="navbar-brand" href="@url('/')" style="margin-right: 0">
            {{$siteName}}
        </a>
        <a class="navbar-brand" href="http://znck.me/">
            <span style="color: rgba(0, 0, 0, 0.25);">&nbsp;| Rahul Kadyan</span>
        </a>
        <div>
            <div class="clearfix hidden-lg-up">
                <a role="button" class="navbar-toggler float-xs-right" data-toggle="collapse"
                   data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false"
                   aria-label="Toggle navigation"></a>
            </div>
            <div class="collapse navbar-toggleable-md" id="navbarResponsive">
                <ul class="nav navbar-nav float-lg-right">
                    @include('nav')
                </ul>
                @yield('extra-nav')
            </div>
        </div>
    </div>
</nav>

<div class="body" style="min-height: 70vh">
    @yield('body')
</div>

<div class="clearfix"></div>

<script src="http://znck.me/assets/js/app.js" type="text/javascript"></script>
</body>
</html>
