@extends('layouts.master')

@section('title')
  Version History
@endsection

@section('content')
  <h1 id="introduction" class="type--header type--thin">Version History</h1>
  <h2>{{ env('APP_NAME') }} 1.0.0 <small>Release Date: 03/29/18</small></h2>
  <p>
    Initial Release
    <br><br>
    <strong>New Features:</strong>
    <ol>
      <li>Ability to retrieve all citations.</li>
      <li>Ability to retrieve all citation types - (article, book, chapter, presentation &amp; thesis).</li>
      <li>Ability to retrieve a specific person's citation.</li>
      <li>Ability to retrieve a specific person's citation type.</li>
    </ol>
  </p>
  <hr>
@endsection
