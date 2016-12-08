# Handle Downloads

**Attach** provides simple function to serve and download attachment.

``` php
...
public function show($filename) {
  // Respond with file stream.
  return serve_attachment($filename);
}

public function download($filename) {
  // Downloads attachment.
  return download_attachment($filename);
}
```

-------------------------------
[Edit this page on Github]({{ $docs_edit_url }}/download.md)
