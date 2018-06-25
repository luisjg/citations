<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Scopus API Key
    |--------------------------------------------------------------------------
    |
    | The API key registered with Scopus.
    |
    | Get an API key: https://dev.elsevier.com/apikey/create
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
    | This value can probably be set as low as 0.2 safely for 4 requests/second
    | depending on how quickly the API endpoints respond. Realistically, though,
    | the performance probably won't be better than 2 or 3 requests per second
    | in a single-threaded PHP system since it's a full request/response
    | transaction.
    |
    | API key settings: https://dev.elsevier.com/api_key_settings.html
    |
    */
  	'niceness' => env('SCOPUS_API_NICENESS', 0.5),

];