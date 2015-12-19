<?php

namespace Cmfcmf\Module\MediaModule\Security;

use Symfony\Component\Translation\TranslatorInterface;

class SecurityTree
{
    /**
     * @var SecurityLevel[]
     */
    private $levels;

    /**
     * @var SecurityCategory[]
     */
    private $categories;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Translation domain.
     *
     * @var string
     */
    private $domain;

    /**
     * @param TranslatorInterface $translator
     * @param $domain
     */
    public function __construct(TranslatorInterface $translator, $domain)
    {
        $this->translator = $translator;
        $this->domain = $domain;

        $this->initCategories();
        $this->initLevels();
    }

    private function initCategories()
    {
        $this->categories = $categories = [
            'no-access' => new SecurityCategory(0, $this->translator->trans('No access', [], $this->domain)),
            'view' => new SecurityCategory(1, $this->translator->trans('View', [], $this->domain)),
            'add' => new SecurityCategory(2, $this->translator->trans('Add', [], $this->domain)),
            'edit' => new SecurityCategory(3, $this->translator->trans('Edit', [], $this->domain)),
            'delete' => new SecurityCategory(4, $this->translator->trans('Delete', [], $this->domain)),
            'permission' => new SecurityCategory(5, $this->translator->trans('Permissions', [], $this->domain)),
        ];
    }

    private function initLevels()
    {
        $levels = [];

        $levels[0] = new SecurityLevel(
            0,
            $this->translator->trans('No access', [], $this->domain),
            $this->translator->trans('Doesn\'t grant any kind of access.', [], $this->domain),
            $this->categories['no-access'],
            [],
            []
        );
        $this->categories['no-access']->addLevel($levels[0]);

        $levels[1] = new SecurityLevel(
            1,
            $this->translator->trans('Subcollection and media overview', [], $this->domain),
            $this->translator->trans('Grants access to view the collection\'s media and subcollections.', [], $this->domain),
            $this->categories['view'],
            [],
            [$levels[0]]
        );
        $this->categories['view']->addLevel($levels[1]);

        $levels[2] = new SecurityLevel(
            2,
            $this->translator->trans('Download all media', [], $this->domain),
            $this->translator->trans('Grants access to download the whole collection at once.', [], $this->domain),
            $this->categories['view'],
            [$levels[1]],
            [$levels[0]]
        );
        $this->categories['view']->addLevel($levels[2]);

        $levels[3] = new SecurityLevel(
            3,
            $this->translator->trans('Display media details', [], $this->domain),
            $this->translator->trans('Grants access to the media details page.', [], $this->domain),
            $this->categories['view'],
            [$levels[1]],
            [$levels[0]]
        );
        $this->categories['view']->addLevel($levels[3]);

        $levels[4] = new SecurityLevel(
            4,
            $this->translator->trans('Download single media', [], $this->domain),
            $this->translator->trans('Grants access to download a single medium.', [], $this->domain),
            $this->categories['view'],
            [$levels[2], $levels[3]],
            [$levels[0]]
        );
        $this->categories['view']->addLevel($levels[4]);

        $levels[5] = new SecurityLevel(
            5,
            $this->translator->trans('Add new media', [], $this->domain),
            $this->translator->trans('Grants access to create new media.', [], $this->domain),
            $this->categories['add'],
            [],
            [$levels[0]]
        );
        $this->categories['add']->addLevel($levels[5]);

        $levels[6] = new SecurityLevel(
            6,
            $this->translator->trans('Add new collections', [], $this->domain),
            $this->translator->trans('Grants access to create new collections.', [], $this->domain),
            $this->categories['add'],
            [$levels[5]],
            [$levels[0]]
        );
        $this->categories['add']->addLevel($levels[6]);

        $levels[7] = new SecurityLevel(
            7,
            $this->translator->trans('Edit media', [], $this->domain),
            $this->translator->trans('Grants access to edit media.', [], $this->domain),
            $this->categories['edit'],
            [$levels[4], $levels[5]],
            [$levels[0]]
        );
        $this->categories['edit']->addLevel($levels[7]);

        $levels[8] = new SecurityLevel(
            8,
            $this->translator->trans('Edit collection', [], $this->domain),
            $this->translator->trans('Grants access to edit the collection.', [], $this->domain),
            $this->categories['edit'],
            [$levels[4], $levels[6]],
            [$levels[0]]
        );
        $this->categories['edit']->addLevel($levels[8]);

        $levels[9] = new SecurityLevel(
            9,
            $this->translator->trans('Edit sub-collections', [], $this->domain),
            $this->translator->trans('Grants access to edit sub-collection.', [], $this->domain),
            $this->translator->trans('Edit', [], $this->domain),
            [$levels[4], $levels[6]],
            [$levels[0]]
        );
        $this->categories['edit']->addLevel($levels[9]);

        $levels[10] = new SecurityLevel(
            10,
            $this->translator->trans('Delete media', [], $this->domain),
            $this->translator->trans('Grants access to delete media.', [], $this->domain),
            $this->categories['delete'],
            [$levels[7]],
            [$levels[0]]
        );
        $this->categories['delete']->addLevel($levels[10]);

        $levels[11] = new SecurityLevel(
            11,
            $this->translator->trans('Delete sub-collections', [], $this->domain),
            $this->translator->trans('Grants access to delete sub-collections.', [], $this->domain),
            $this->categories['delete'],
            [$levels[8]],
            [$levels[0]]
        );
        $this->categories['delete']->addLevel($levels[11]);

        $levels[12] = new SecurityLevel(
            12,
            $this->translator->trans('Delete collection', [], $this->domain),
            $this->translator->trans('Grants access to delete the collection.', [], $this->domain),
            $this->categories['delete'],
            [$levels[11]],
            [$levels[0]]
        );
        $this->categories['delete']->addLevel($levels[12]);

        $levels[13] = new SecurityLevel(
            13,
            $this->translator->trans('Widen permissions', [], $this->domain),
            $this->translator->trans('Allows to widen the permissions.', [], $this->domain),
            $this->categories['permission'],
            [$levels[12], $levels[7]],
            [$levels[0]]
        );
        $this->categories['permission']->addLevel($levels[13]);

        $this->levels = $levels;
    }

    /**
     * @return SecurityCategory[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return SecurityLevel[]
     */
    public function getLevels()
    {
        return $this->levels;
    }
}