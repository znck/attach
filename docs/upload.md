# Handle Uploads

Use `Znck\Attach\Builder` to fluently upload a file.

``` php
...
use Znck\Attach\Builder;

class UploadController {
  ...

  public function store(Request $request) {
    $attachment = Builder::make($request)->upload('uploads/files')->getAttachment();

    return attach_url($attachment);
  }
}
```

You can resize images.

``` php
Builder::make($request)
  ->resize(1200)
  ->upload('uploads/files')
```

You can create multiple sizes of images.

``` php
Builder::make($request)
  ->resize(256, 'small')
  ->resize(600, 'medium')
  ->resize(1200, 'large')
  ->resize(2400, 'retina')
  ->upload('uploads/files')
```

And links are generated with same ease.

``` php
attach_url($attachment, 'small'); // For small image.
```

You can resize images on queues.

``` php
Builder::make($request)
  ->queue()->resize(256, 'small')
  ->queue()->resize(600, 'medium')
  ->queue()->resize(1200, 'large') // This would run on queue.
  ->resize(2400) // This would run in sync.
  ->upload('uploads/files')
```

-------------------------------
[Edit this page on Github]({{ $docs_edit_url }}/upload.md)
