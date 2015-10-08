# Running tests

The Media Module comes with a couple of PHPUnit tests. These can be run using PHPUnit. First, make sure the module
is active and installed in a running Zikula installation. Then execute the following command from within the Zikula
webroot:

```
phpunit --configuration modules\cmfcmf\media-module\phpunit.xml.dist
```
Tests should now run (and pass, of course).
