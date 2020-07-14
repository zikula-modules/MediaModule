---
title: "Font handling"
excerpt: "When creating text-based watermarks, the user can select from a variety of fonts. Learn how to add custom fonts in this entry."
---

If you feel like the provided fonts don't fit your needs, you have two options to add other `*.ttf` fonts.

1. Add a custom font loader from within your bundle (preferred).
2. Copy `*.ttf` files into the `Resources/fonts` folder (not preferred, as it's going to be overridden by the next 
module upgrade). 

## Adding a custom font loader

Requirements:

- [What is a service](http://symfony.com/doc/current/book/service_container.html#what-is-a-service)
- [How to define a service](http://symfony.com/doc/current/book/service_container.html#creating-configuring-services-in-the-container)
- [How to define a service with a custom tag](http://symfony.com/doc/current/components/dependency_injection/tags.html#define-services-with-a-custom-tag)

Inside your module, add a new service and tag it with `cmfcmf_media_module.font`. The service must implement the
`\Cmfcmf\Module\MediaModule\Font\FontLoaderInterface`:

```php?start_inline=1
/**
 * @return FontInterface[]
 */
public function loadFonts();
```

The interface contains only one method. The method is called once when fonts are requested and must return
an array of classes implementing the `\Cmfcmf\Module\MediaModule\Font\FontInterface`. The interface itself
is pretty straight forward:

```php?start_inline=1
namespace Cmfcmf\Module\MediaModule\Font;

interface FontInterface
{
    /**
     * The font title.
     */
    public function getTitle(): string;

    /**
     * The font id. Must be unique!
     */
    public function getId(): string;

    /**
     * The path to the .ttf file.
     */
    public function getPath(): string;

    /**
     * Returns the font name of the corresponding Google font. Returns null if it doesn't exist.
     */
    public function getGoogleFontName(): ?string;
}
```

- The **title** is used when displaying the font selector to the user.
- The **id** must be unique across all bundles and font loaders. It's recommended to prepend your bundle's name,
  for example: `myfoobundle:foo_font`. 
- The **path** represents the full path to the `*.ttf` file.
- The optional **googleFontName** should be returned if the font is also hosted at 
  [Google Fonts](https://www.google.com/fonts). It must correspond to the name used in the `<link>` tag to include 
  the font. You can see the `<link>` tag at the "Use" page of Google Fonts.
  If provided, the Google font will be included on the text-based watermark creation page so that the user can
  preview the font.
