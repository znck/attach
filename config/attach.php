<?php return [
    /*
    |--------------------------------------------------------------------------
    | URI Generator
    |--------------------------------------------------------------------------
    |
    | Here you may define route name and parameters required to generate URL.
    | Parameters any field available on `model` object.
    |  
    */
    'uri' => [
        'generator'  => \Znck\Attach\UriGenerator::class,
        'name'       => 'media',
        'parameters' => [
            'id' => 'filename', // Use `id` as `filename` in parameters.
        ],
        'middleware' => [],
    ],

    /*
     |--------------------------------------------------------------------------
     | Token Generator
     |--------------------------------------------------------------------------
     |
     | It is useful in stateless application to control/limit media access.
     |
     */
    'token' => [
        'generator' => \Znck\Attach\TokenGenerators\EncryptedHash::class
    ],

    /*
     |--------------------------------------------------------------------------
     | Model Class
     |--------------------------------------------------------------------------
     |
     | This is Eloquent model implementing Znck\Attach\Contacts\Media interface.
     |
     */
    'model' => \Znck\Attach\Attachment::class,

    /*
     |--------------------------------------------------------------------------
     | Manipulation Manager
     |--------------------------------------------------------------------------
     |
     | Manager applies required manipulations to Media object.
     | It is should Znck\Attach\Contacts\Manager interface.
     |
     */
    'manager' => \Znck\Attach\Manager::class,

    /*
     |--------------------------------------------------------------------------
     | Upload Location
     |--------------------------------------------------------------------------
     |
     | Configure disk and path for uploading files.
     |
     */
    'upload' => [
        'disk' => null,
        'path' => storage_path('uploads'),
    ],
];
