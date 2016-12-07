# Installation

Use [Composer](https://getcomposer.com){target=_blank} to install **attach**.

``` bash
composer require znck/attach
```

Next, your need to register the service provider,

``` php
// config/app.php

'providers' => [
  ...
  Znck\Attach\AttachServiceProvider::class,
],
```

Run the migrations,

``` bash
php artisan migrate
```

And finally, generate a signing key.

``` bash
php artisan attach:key
```

Optionally, you can publish migrations. See [advanced usage]({{ $docs_url }}/publish).


-------------------------------
[Edit this page on Github]({{ $docs_edit_url }}/installation.md)
