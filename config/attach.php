<?php return [
    /*
    |--------------------------------------------------------------------------
    | Attachment Model
    |--------------------------------------------------------------------------
    |
    | This option defines the attachment model.
    |
    */
    'model' => \Znck\Attach\Util\Attachment::class,

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
        'key'    => env('URL_SIGNING_KEY', env('APP_KEY')),
        'expiry' => null,
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
