---
title: "Tests"
excerpt: "The module uses PHPUnit to test it's code. Tests are run automatically whenever someone pushes to the GitHub repository. This entry explains how to run tests locally."
---

The Media Module comes with a couple of PHPUnit tests. These can be run using PHPUnit. First, make sure the module
is active and installed in a running Zikula installation. Then execute the following command from within the Zikula
webroot:


{% highlight bash %}
$ phpunit --configuration src/extensions/cmfcmf/media-module/phpunit.xml.dist
{% endhighlight %}

Tests should now run (and pass, of course).
