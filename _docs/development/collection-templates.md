---
title: "Collection templates"
excerpt: "Learn how to create your own collection templates."
---

If you feel like the provided templates don't fit your needs, you can easily add more custom templates from within _your_ module.

Requirements:

- [What is a service](http://symfony.com/doc/current/book/service_container.html#what-is-a-service)
- [How to define a service](http://symfony.com/doc/current/book/service_container.html#creating-configuring-services-in-the-container)
- [How to define a service with a custom tag](http://symfony.com/doc/current/components/dependency_injection/tags.html#define-services-with-a-custom-tag)

Inside your module, add a new class implementing the `TemplateInterface`:

```php?start_inline=1
namespace Cmfcmf\Module\MediaModule\CollectionTemplate;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\MediaType\MediaTypeCollection;
use Symfony\Component\HttpFoundation\Response;

interface TemplateInterface
{
    /**
     * Renders the template with the given collection.
     *
     * @param CollectionEntity    $collectionEntity     The collection to render.
     * @param MediaTypeCollection $mediaTypeCollection  A collection of media types.
     * @param bool                $showChildCollections Whether or not to show child collections.
     *
     * @return Response
     */
    public function render(
        CollectionEntity $collectionEntity,
        MediaTypeCollection $mediaTypeCollection,
        $showChildCollections
    );

    /**
     * A unique name which is used to refer to the template in the database.
     * It is best practice to prefix it with your module's name.
     *
     * @return string
     */
    public function getName();

    /**
     * The title of the collection template. It is shown when the user selects the template
     * to use for a collection.
     *
     * @return string
     */
    public function getTitle();
}
```

Register it as a service and tag it with `cmfcmf_media_module.collection_template`. Do **not** inherit from the internal `AbstractTemplate` class!
