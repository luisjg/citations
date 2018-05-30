@extends('layouts.master')

@section('title')
  Version History
@endsection

@section('content')
  <h1 id="introduction" class="type--header type--thin">Version History</h1>
  <h2>{{ env('APP_NAME') }} 1.1.0 <small>Release Date: TBD</small></h2>
  <p>
    Additional functionality for retrieval of groups of information
    <br /><br />
    <strong>New Features:</strong>
    <ol>
      <li>Ability to retrieve all citations for a given department.</li>
      <li>Ability to retrieve all citations for a given college.</li>
      <li>Ability to limit the citations to the most recent X entries with a filter.</li>
    </ol>
  </p>
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
