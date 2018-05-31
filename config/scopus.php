<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Scopus API Key
    |--------------------------------------------------------------------------
    |
    | The API key registered with Scopus.
    |
    */
  	'key' => env('SCOPUS_API_KEY', null),

  	/*
    |--------------------------------------------------------------------------
    | Scopus API "Niceness"
    |--------------------------------------------------------------------------
    |
    | The amount of time (in seconds) to wait between requests for the Scopus
    | Search API. The rate limiting allows for 6 requests/second as it stands
    | right now.
    |
    | API key settings: https://dev.elsevier.com/api_key_settings.html
    |
    */
  	'niceness' => env('SCOPUS_API_NICENESS', 0.5),

];