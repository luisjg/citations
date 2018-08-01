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
    "formatted": "A. Gottfried, "Motivational roots of leadership: A longitudinal study from childhood through adulthood", <em>The Leadership Quarterly</em>, 2011.",
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
          "profile": "https://www.metalab.csun.edu/faculty/profiles/adele.gottfried",
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
  <li><strong>GENERATE THE URI:</strong> Find the usage that fits your need. Browse through collections and subcollections to help you craft your URI.</li>
  <li><strong>ADD OPTIONAL FILTERS:</strong> Add any optional query filters to limit/manipulate your data. See the Query Filters section. One or more filters can be added to any query.</li>
  <li><strong>PROVIDE THE DATA:</strong> Use the URI to query your data. See the Usage Example section.</li>
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
<strong>All Citations per Department/College</strong>
<ul>
  <li><a href="{!! url('1.1/departments/189/citations') !!}">{!! url('1.1/departments/189/citations') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations') !!}">{!! url('1.1/colleges/52/citations') !!}</a></li>
</ul>
<strong>Specific Citation Type per Department</strong>
<ul>
  <li><a href="{!! url('1.1/departments/189/citations/articles') !!}">{!! url('1.1/departments/189/citations/articles') !!}</a></li>
  <li><a href="{!! url('1.1/departments/189/citations/books') !!}">{!! url('1.1/departments/189/citations/books') !!}</a></li>
  <li><a href="{!! url('1.1/departments/189/citations/chapters') !!}">{!! url('1.1/departments/189/citations/chapters') !!}</a></li>
  <li><a href="{!! url('1.1/departments/189/citations/presentations') !!}">{!! url('1.1/departments/189/citations/presentations') !!}</a></li>
  <li><a href="{!! url('1.1/departments/189/citations/theses') !!}">{!! url('1.1/departments/189/citations/theses') !!}</a></li>
</ul>
<strong>Specific Citation Type per College</strong>
<ul>
  <li><a href="{!! url('1.1/colleges/52/citations/articles') !!}">{!! url('1.1/colleges/52/citations/articles') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/books') !!}">{!! url('1.1/colleges/52/citations/books') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/chapters') !!}">{!! url('1.1/colleges/52/citations/chapters') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/presentations') !!}">{!! url('1.1/colleges/52/citations/presentations') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/theses') !!}">{!! url('1.1/colleges/52/citations/theses') !!}</a></li>
</ul>
<h2 id="query-filters" class="type--header type--thin">Query Filters</h2>
<strong>Recent Number of Citations per Department/College</strong>
<ul>
  <li><a href="{!! url('1.1/departments/189/citations?recent=20') !!}">{!! url('1.1/departments/189/citations?recent=20') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations?recent=20') !!}">{!! url('1.1/colleges/52/citations?recent=20') !!}</a></li>
</ul>
<strong>Citations per Department/College by Date</strong>
<ul>
  <li><a href="{!! url('1.1/departments/189/citations/theses?date=2014') !!}">{!! url('1.1/departments/189/citations/theses?date=2014') !!}</a></li>
  <li><a href="{!! url('1.1/departments/189/citations/theses?date=2014-06') !!}">{!! url('1.1/departments/189/citations/theses?date=2014-06') !!}</a></li>
  <li><a href="{!! url('1.1/departments/189/citations/theses?date=2014-06-05') !!}">{!! url('1.1/departments/189/citations/theses?date=2014-06-05') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/theses?date=2014') !!}">{!! url('1.1/colleges/52/citations/theses?date=2014') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/theses?date=2014-06') !!}">{!! url('1.1/colleges/52/citations/theses?date=2014-06') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/theses?date=2014-06-05') !!}">{!! url('1.1/colleges/52/citations/theses?date=2014-06-05') !!}</a></li>
</ul>
<strong>Sorted Citations per Department/College by Author Last Name</strong>
<ul>
  <li><a href="{!! url('1.1/departments/189/citations/theses?sortBy=author_lastname&sortDir=ASC') !!}">{!! url('1.1/departments/189/citations/theses?sortBy=author_lastname&sortDir=ASC') !!}</a></li>
  <li><a href="{!! url('1.1/departments/189/citations/theses?sortBy=author_lastname&sortDir=DESC') !!}">{!! url('1.1/departments/189/citations/theses?sortBy=author_lastname&sortDir=DESC') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/theses?sortBy=author_lastname&sortDir=ASC') !!}">{!! url('1.1/colleges/52/citations/theses?sortBy=author_lastname&sortDir=ASC') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/theses?sortBy=author_lastname&sortDir=DESC') !!}">{!! url('1.1/colleges/52/citations/theses?sortBy=author_lastname&sortDir=DESC') !!}</a></li>
</ul>
<strong>Sorted Citations per Department/College by Date</strong>
<ul>
  <li><a href="{!! url('1.1/departments/189/citations/theses?sortBy=date&sortDir=ASC') !!}">{!! url('1.1/departments/189/citations/theses?sortBy=date&sortDir=ASC') !!}</a></li>
  <li><a href="{!! url('1.1/departments/189/citations/theses?sortBy=date&sortDir=DESC') !!}">{!! url('1.1/departments/189/citations/theses?sortBy=date&sortDir=DESC') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/theses?sortBy=date&sortDir=ASC') !!}">{!! url('1.1/colleges/52/citations/theses?sortBy=date&sortDir=ASC') !!}</a></li>
  <li><a href="{!! url('1.1/colleges/52/citations/theses?sortBy=date&sortDir=DESC') !!}">{!! url('1.1/colleges/52/citations/theses?sortBy=date&sortDir=DESC') !!}</a></li>
</ul>
<strong>Specified Person's Citations</strong>
<ul>
  <li><a href="{!! url('1.0/citations?email='.$email) !!}">{!! url('1.0/citations?email='.$email) !!}</a></li>
  <li><a href="{!! url('1.0/citations?orcid='.$orcid) !!}">{!! url('1.0/citations?orcid='.$orcid) !!}</a></li>
</ul>
<strong>Specific Citation Type by user</strong>
<ul>
  <li><a href="{!! url('1.0/citations/articles?email='.$email) !!}">{!! url('1.0/citations/articles?email='.$email) !!}</a></li>
  <li><a href="{!! url('1.0/citations/books?email='.$email) !!}">{!! url('1.0/citations/books?email='.$email) !!}</a></li>
  <li><a href="{!! url('1.0/citations/chapters?email='.$email) !!}">{!! url('1.0/citations/chapters?email='.$email) !!}</a></li>
  <li><a href="{!! url('1.0/citations/presentations?email='.$email) !!}">{!! url('1.0/citations/presentations?email='.$email) !!}</a></li>
  <li><a href="{!! url('1.0/citations/theses?email='.$email) !!}">{!! url('1.0/citations/theses?email='.$email) !!}</a></li>
</ul>
<strong>Specific Citation Type by User by Date</strong>
<ul>
  <li><a href="{!! url('1.0/citations/articles?email='.$email.'&date=2015') !!}">{!! url('1.0/citations/articles?email='.$email.'&date=2015') !!}</a></li>
  <li><a href="{!! url('1.0/citations/theses?email='.$email.'&date=2013') !!}">{!! url('1.0/citations/theses?email='.$email.'&date=2013') !!}</a></li>
</ul>
<strong>Specific Citation Type by User, Sorted by Date</strong>
<ul>
  <li><a href="{!! url('1.0/citations/theses?email='.$email.'&sortBy=date&sortDir=ASC') !!}">{!! url('1.0/citations/theses?email='.$email.'&sortBy=date&sortDir=ASC') !!}</a></li>
  <li><a href="{!! url('1.0/citations/theses?email='.$email.'&sortBy=date&sortDir=DESC') !!}">{!! url('1.0/citations/theses?email='.$email.'&sortBy=date&sortDir=DESC') !!}</a></li>
</ul>

<h2 class="type--header type--thin" id="examples">Usage Example</h2>
  <dl class="accordion">
    <dt class="accordion__header"> JavaScript <i class="fa fa-chevron-down fa-pull-right type--red" aria-hidden="true"></i></dt>
    <dd class="accordion__content">
        <pre>
        <code class="prettyprint lang-javascript">


// this example assumes jQuery integration for ease of use
// and a &lt;div&gt; element with the ID of "citation-results"

// query all article citations
var url = '{!! url('1.0/citations/articles') !!}';
$(document).ready(function() {

  // perform a shorthand AJAX call to grab the information
  $.get(url, function(data) {

    // iterate over the returned citations
    var citations = data.articles;
    $(citations).each(function(index, article) {

      // append each citation to the content of the element
      $('#citation-results').append('&lt;p&gt;' + article.metadata.title + ' - ' + article.metadata.journal + '&lt;/p&gt;');

    });
    
  });

});
      </code>
      </pre>
    </dd>
    <dt class="accordion__header"> PHP <i class="fa fa-chevron-down fa-pull-right type--red" aria-hidden="true"></i></dt>
    <dd class="accordion__content">
      <pre>
      <code class="prettyprint lang-php">
// query all article citations
$url = '{!! url('1.0/citations/articles') !!}';

// call url, you can also use CURL or guzzle -> https://github.com/guzzle/guzzle
$data = file_get_contents($url);

// decode into an array
$data = json_decode($data, true);

// setup a blank array
$citation_list = [];

// loop through results
foreach($data['articles'] as $article){
  $citation_list[] = $article['metadata']['title'].' - '.$article['metadata']['journal'];
}

print_r($citation_list);
      </code>
      </pre>
    </dd>
    <dt class="accordion__header"> Python <i class="fa fa-chevron-down fa-pull-right type--red" aria-hidden="true"></i></dt>
    <dd class="accordion__content">
        <pre>
        <code class="prettyprint lang-python">
#python
import urllib2
import json

#query all article citations
url = u'{!! url('1.0/citations/articles') !!}'

#try to read the data 
try:
   u = urllib2.urlopen(url)
   data = u.read()
except Exception as e:
  data = {}

#decode into an array
data = json.loads(data)

#setup a blank array
citation_list = []

#loop through results
for article in data['articles']:
  citation_list.append(article['metadata']['title'] + ' ' + article['metadata']['journal'])

print citation_list
      </code>
      </pre>
    </dd>
    <dt class="accordion__header"> Ruby <i class="fa fa-chevron-down fa-pull-right type--red" aria-hidden="true"></i></dt>
    <dd class="accordion__content">
        <pre>
        <code class="prettyprint lang-ruby">
require 'net/http'
require 'json'

#query all article citations
source = '{!! url('1.0/citations/articles') !!}'

#call data
response = Net::HTTP.get_response(URI.parse(source))

#get body of the response
data = response.body

#put the parsed data
puts JSON.parse(data)
      </code>
        </pre>
    </dd>
  </dl>
@endsection