<!DOCTYPE HTML>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <meta name="description" content="@yield('description')">
    <title>{{ env('APP_NAME') }} Web Service | @yield('title')</title>
    <link rel="icon" href="//www.csun.edu/sites/default/themes/csun/favicon.ico" type="image/x-icon"/>
    <script src="//use.typekit.net/gfb2mjm.js"></script>
    <script>try{Typekit.load();}catch(e){}</script>
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Open+Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic"/>
    <link rel="stylesheet" href="{{ url('/css/metaphor.css') }}"/>
    <link rel="stylesheet" href="{{ url('/css/tomorrow.css.min') }}"/>
    @yield('page-styles')
</head>
<body>
<div class="section section--sm">
  <div class="container type--center">
    <h1 class="giga type--thin">{{ env('APP_NAME') }} Web Service</h1>
    <h3 class="h1 type--thin type--gray">Delivering Citations of Published Works</h3>
  </div>
</div>
<div class="main main--metalab" style="min-height: calc(100vh - 130px);">

    <div class="section" id="menu">
        <div class="container">
            <div class="row">
                <div class="col-md-3" id="sidebar">
                    @include('layouts.partials.side-nav')
                </div>
                <div class="col-md-9" id="page">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.partials.csun-footer')
@include('layouts.partials.metalab-footer')
<script src="{{ url('/js/metaphor.js') }}"></script>
<script src="{{ url('/js/run_prettify.js') }}"></script>
<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>

<!--
  __  __   ___   _____     _
 |  \/  | | __| |_   _|   /_\       Explore Learn Go Beyond
 | |\/| | | _|    | |    / _ \      https://www.metalab.csun.edu/
 |_|  |_| |___|   |_|   /_/ \_\
    _       _        _     ___
  _| |_    | |      /_\   | _ )
 |_   _|   | |__   / _ \  | _ \
   |_|     |____| /_/ \_\ |___/
-->
</body>
</html>
