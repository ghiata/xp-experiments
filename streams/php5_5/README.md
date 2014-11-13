JDK8 Streams in PHP
===================

The [java.util.stream package](http://download.java.net/jdk8/docs/api/java/util/stream/package-summary.html) implemented with [PHP 5.5 generators](http://www.php.net/manual/en/class.generator.php).

Examples
--------

```php
$return= Stream::of([1, 2, 3, 4])
  ->filter(function($e) { return 0 === $e % 2; })
  ->toArray()
;
// [2, 4]

$return= Stream::of([1, 2, 3, 4])
  ->map(function($e) { return $e * 2; })
  ->toArray()
);
// [2, 4, 6, 8]
```

Further reading
---------------

* [JDK8: Stream style](http://de.slideshare.net/SergeyKuksenko/jdk8-stream-style) - by Sergey Kuksenko, Performance Engineering at Oracle on Dec 03, 2013 