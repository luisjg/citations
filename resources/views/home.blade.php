@extends('layouts.master')

@section('title')
  Documentation
@endsection

@section('content')
<h2 id="introduction" class="type--header type--thin">Introduction</h2>
<p>The {{ env('APP_NAME') }} web service is an integral part of the Citations Manager which
  allows for the retrieval of published works.
  This information is derived from many on-campus sources as well as faculty submitted
  information using the Citations Manager.
  The web service follows the REST-ful design where the information is retrieved by
  creating a specific URI and giving values to filter the data. The information that is
  returned is a JSON object that contains a set of interest or badges attached to a particular
  member; the format of the JSON object is as follows:
</p>
<pre class="prettyprint"><code>{
  "status": "200",
  "success": "true",
  "api": "citations",
  "version": "1.0",
  "collection": "articles",
  "count": "1",
  "article": {
    "citation_id": "citations:1",
    "entities_id": null,
    "collaborators": null,
    "citation_text": "Gottfried, Adele. E., et al. (2011). Motivational roots of leadership: A longitudinal study from childhood through adulthood. The Leadership Quarterly, 22, 510-519. In special issue of The Leadership Quarterly on &ldquo;Longitudinal Investigations of Leader Development.&rdquo;",
    "note": null,
    "membership": {
      "type": "public",
      "members": [
        {
          "user_id": "members:100010571",
          "orcid": null,
          "email": "adele.gottfried@csun.edu",
          "display_name": "Adele E Gottfried",
          "first_name": "Adele",
          "middle_name": null,
          "last_name": "Gottfried",
          "profile": "https://academics.csun.edu/faculty/adele.gottfried",
          "precedence": "0",
          "role": "author"
        }
      ]
    },
    "published": {
      "how": null,
      "date": "2011"
    },
    "is_published": "false",
    "type": "article",
    "metadata": {
      "title": "Motivational roots of leadership: A longitudinal study from childhood through adulthood",
      "abstract": "The present study elucidates developmental roots of leadership by investigating how motivation from childhood through adolescence is linked to motivation to lead in adulthood. Results showed considerable and significant continuity between academic intrinsic motivation and motivation to lead, indicating that adults with greater enjoyment of leadership per se, and who are motivated to lead without regard to external consequences, were significantly more intrinsically motivated from childhood through adolescence. Implications for developing motivation in leaders are advanced.",
      "book_title": null,
      "journal": "The Leadership Quarterly"
    },
    "collection": null,
    "document": null,
    "publisher": null
  }
}</code></pre>
<br>
<h2 id="getting-started" class="type--header type--thin">Getting Started</h2>
<ol>
  <li><strong>GENERATE THE URI:</strong> Find the usage that fits your need. Browse through subcollections, instances and query types to help you craft your URI.</li>
  <li><strong>PROVIDE THE DATA:</strong> Use the URI to query your data. See the Usage Example session.</li>
  <li><strong>SHOW THE RESULTS</strong></li>
</ol>
<p>Loop through the data to display its information. See the Usage Example session.</p>
<br>
<h2 id="collections" class="type--header type--thin">Collections</h2>
<strong>All Citations Listing</strong>
<ul>
  <li><a href="{!! url('1.0/citations') !!}">{!! url('1.0/citations') !!}</a></li>
</ul>
<strong>Specific Citation Type</strong>
<ul>
  <li><a href="{!! url('1.0/citations/articles') !!}">{!! url('1.0/citations/articles') !!}</a></li>
  <li><a href="{!! url('1.0/citations/books') !!}">{!! url('1.0/citations/books') !!}</a></li>
  <li><a href="{!! url('1.0/citations/chapters') !!}">{!! url('1.0/citations/chapters') !!}</a></li>
  <li><a href="{!! url('1.0/citations/presentations') !!}">{!! url('1.0/citations/presentations') !!}</a></li>
  <li><a href="{!! url('1.0/citations/theses') !!}">{!! url('1.0/citations/theses') !!}</a></li>
</ul>
<h2 id="subcollections" class="type--header type--thin">Subcollections</h2>
<strong>Specified person's Citations</strong>
<ul>
  <li><a href="{!! url('1.0/citations?email='.$email) !!}">{!! url('1.0/citations?email='.$email) !!}</a></li>
</ul>
<strong>Specific Citation Type by user</strong>
<ul>
  <li><a href="{!! url('1.0/citations/articles?email='.$email) !!}">{!! url('1.0/citations/articles?email='.$email) !!}</a></li>
  <li><a href="{!! url('1.0/citations/books?email='.$email) !!}">{!! url('1.0/citations/books?email='.$email) !!}</a></li>
  <li><a href="{!! url('1.0/citations/chapters?email='.$email) !!}">{!! url('1.0/citations/chapters?email='.$email) !!}</a></li>
  <li><a href="{!! url('1.0/citations/presentations?email='.$email) !!}">{!! url('1.0/citations/presentations?email='.$email) !!}</a></li>
  <li><a href="{!! url('1.0/citations/theses?email='.$email) !!}">{!! url('1.0/citations/theses?email='.$email) !!}</a></li>
</ul>
@endsection