Zikula 1.4.1+ MediaModule by @cmfcmf
====================================

[![StyleCI](https://styleci.io/repos/43518681/shield)](https://styleci.io/repos/43518681) 
[![Build Status](https://travis-ci.org/cmfcmf/MediaModule.svg?branch=master)](https://travis-ci.org/cmfcmf/MediaModule) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cmfcmf/MediaModule/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cmfcmf/MediaModule/?branch=master)
[![MIT License](https://img.shields.io/github/license/cmfcmf/MediaModule.svg)]()

The module supports many media types natively (images, plaintext, MarkDown, videos, audio, pdf, …)
and further types can be added with ease! 

Not only can you upload media, you can also search for and embed all kinds of media from the web!
Here are some: Tweets, YouTube videos, Music, Flickr images, …

You can create text- and image-based watermarks to watermark all uploaded images.
The watermarks scale automatically and can be exchanged at any time. 
The original images are preserved. 

Rendered MarkDown files? Syntax-highlighted source code? Automatic updates?
Thumbnail generation? Works on mobile? YES. All of this. Read on or download the module already! 

**Download the module from https://github.com/cmfcmf/MediaModule/releases/latest 
(requires Zikula 1.4.1+, which is not yet released. Until then, you could use 
[a preview build](http://zikula.org/library/releases) to test it)!**

Install to `modules/cmfcmf/media-module` (but other locations should work aswell!).

Read all about the module [at it's website](http://cmfcmf.github.io/MediaModule).

> **Downloading directly from master would require you to execute `composer install --no-dev`.**

## Known issues
- **You MAY NOT allow untrusted users to upload files. It is currently NOT SAFE to do so.**
- you must not create collections with any of the following tittles: *f*, *media*, *settings*, *admin*, *hooks*, *licenses*, *watermarks*. If you do so, it's going to break URLs.
- The Flickr MediaType is currently disabled due to legal questions
- Even though files are watermarked, they are still available non-watermarked. Users could try to guess
  the URL and access the un-watermarked files.
- Permissions are currently only object-type based and not object-instance based. This means that you
  cannot configure permissions per object, but only per object type. I.e. you cannot grant groups
  access to collection A while not granting access to collection B.

## Talk to me!
If you find a bug or have problems, please [create an issue](https://github.com/cmfcmf/MediaModule/issues/new)!

## License and module development

The code is MIT licensed, see the `License.md` file for further information.

*It took me quite some time to create this module. I don't really need it for myself, but I didn't
want another year of Zikula without a proper Media Module. That's why I created it. I'm happy about
all possible support I can get from you, especially if you earn money with the module.*
