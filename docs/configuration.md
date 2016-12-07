# Configuration

Attach works out of the box. You can skip this and go ahead. Sometimes, you may need to tweak some configs to match your requirements.

``` php
// Default Attach Configuration
return [
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
    'sign' => true,
    'signing' => [
        'key' => env('URL_SIGNING_KEY', env('APP_KEY')),
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
        'as' => 'attach::serve',
        'uses' => \Znck\Attach\Util\AttachController::class.'@serve',
    ],
];
```

You can create a file `config/attach.php` and override any of these configs.

Optionally, you can publish this config file. See [advanced usage]({{ $docs_url }}/publish).

-------------------------------
[Edit this page on Github]({{ $docs_edit_url }}/configuration.md)
