@extends('layouts.master')

@section('title')
  Version History
@endsection

@section('content')
  <h1 id="introduction" class="type--header type--thin">Version History</h1>
  <h2>{{ env('APP_NAME') }} 1.2.0 <small>Release Date: 08/02/2018</small></h2>
  <p>
    Additional functionality to filter retrieved information
    <br /><br />
    <strong>New Features:</strong>
    <ol>
      <li>Ability to filter the results of any query by year, month, or day</li>
      <li>Ability to limit the number of results of any multi-result query</li>
      <li>Ability to sort the results of any query by author last name</li>
      <li>Ability to sort the results of any query by date</li>
    </ol>
  </p>
  <h2>{{ env('APP_NAME') }} 1.1.0 <small>Release Date: 06/28/2018</small></h2>
  <p>
    Additional functionality for retrieval / storage of groups of information
    <br /><br />
    <strong>New Features:</strong>
    <ol>
      <li>Ability to retrieve all citations for a given department.</li>
      <li>Ability to retrieve all citations for a given college.</li>
      <li>Ability to limit the citations to the most recent X entries with a filter.</li>
      <li>Ability to retrieve all citations for a given ORCID.</li>
      <li>Ability to import citation metadata from Scopus via ORCID or Scopus author ID.</li>
      <li>Auto-formatting of citation records in IEEE format by default.</li>
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
