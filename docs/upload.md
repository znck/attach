# Handle Uploads

- [Simple Upload](#simple)
- [Image Upload](#image)

{#simple}
## [](#simple) Simple Upload

``` php
...
use Znck\Attach\Builder;

class UploadController {
  ...

  public function store(Request $request) {
    Builder::make($request)->upload(['path' => 'uploads/'])
  }
}
```

-------------------------------
[Edit this page on Github]({{ $docs_edit_url }}/upload.md)
