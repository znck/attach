<?php return [
    /*
    |--------------------------------------------------------------------------
    | Attachment Model
    |--------------------------------------------------------------------------
    |
    | This option defines the attachment model.
    |
    */
    'model' => 'App\Attachment',

    /*
    |--------------------------------------------------------------------------
    | Signed Urls
    |--------------------------------------------------------------------------
    |
    | This option defines the secret key for URL signing.
    |
    */
    'sign'    => true,
    'signing' => [
        'key'    => env('URL_SIGNING_KEY'),
        'expiry' => 0, // In minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Attachment Route
    |--------------------------------------------------------------------------
    |
    | Attachment route can take two values.
    |
    |  array => create route with these values
    |           required: _path, as, uses
    |  string => route name
    */
    'route' => [
        '_path' => '/attach/{filename}',
        'as'    => 'attach::serve',
        'uses'  => \Znck\Attach\Util\AttachController::class.'@serve',
    ],
];
