<?php

namespace Cmfcmf\Module\MediaModule\Form\Media;

use Cmfcmf\Module\MediaModule\Entity\Collection\CollectionEntity;
use Cmfcmf\Module\MediaModule\Entity\License\LicenseEntity;
use Cmfcmf\Module\MediaModule\Form\AbstractType;
use Cmfcmf\Module\MediaModule\Form\DataTransformer\ArrayToJsonTransformer;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\I18n\TranslatableInterface;

abstract class AbstractMediaEntityType extends AbstractType implements TranslatableInterface
{
    /**
     * @var bool
     */
    protected $isCreation;

    /**
     * @var CollectionEntity|null
     */
    private $parent;

    /**
     * @var bool
     */
    private $allowTemporaryUploadCollection;

    public function __construct($isCreation = false, CollectionEntity $parent = null, $allowTemporaryUploadCollection = false)
    {
        parent::__construct();

        $this->isCreation = $isCreation;
        $this->parent = $parent;
        $this->allowTemporaryUploadCollection = $allowTemporaryUploadCollection;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $escapingStrategy = \ModUtil::getVar('CmfcmfMediaModule', 'descriptionEscapingStrategyForMedia');
        switch ($escapingStrategy) {
            case 'raw':
                $descriptionHelp = $this->__('You may use HTML.');
                break;
            case 'text':
                $descriptionHelp = $this->__('Only plaintext allowed.');
                break;
            case 'markdown':
                $descriptionHelp = $this->__('You may use MarkDown.');
                break;
            default:
                throw new \LogicException();
        }

        $hiddenAttr = [
            'class' => 'hidden',
        ];

        $allowTemporaryUploadCollection = $this->allowTemporaryUploadCollection;
        $collectionOptions = [
            'required' => true,
            'label' => $this->__('Collection'),
            'class' => 'CmfcmfMediaModule:Collection\CollectionEntity',
            'query_builder' => function(EntityRepository $er) use ($allowTemporaryUploadCollection) {
                $qb = $er->createQueryBuilder('c');
                $qb
                    ->orderBy('c.root', 'ASC')
                    ->addOrderBy('c.lft', 'ASC')
                ;
                if (!$allowTemporaryUploadCollection) {
                    $qb->where($qb->expr()->not($qb->expr()->eq('c.id', ':uploadCollectionId')))
                        ->setParameter('uploadCollectionId', CollectionEntity::TEMPORARY_UPLOAD_COLLECTION_ID);
                    ;
                }

                return $qb;
            },
            'placeholder' => $this->__('Select collection'),
            'property' => 'indentedTitle',
        ];
        if ($this->parent !== null) {
            $collectionOptions['data'] = $this->parent;
        }

        $builder
            ->add('collection', 'entity', $collectionOptions)
            ->add('title', isset($options['hiddenFields']) && in_array('title', $options['hiddenFields']) ? 'hidden' : 'text', [
                'label' => $this->__('Title')
            ])
            ->add('description', isset($options['hiddenFields']) && in_array('description', $options['hiddenFields']) ? 'hidden' : 'textarea', [
                'required' => false,
                'label' => $this->__('Description'),
                'attr' => [
                    'help' => $descriptionHelp
                ]
            ])
            ->add('license', 'entity', [
                'required' => false,
                'label' => $this->__('License'),
                'class' => 'CmfcmfMediaModule:License\LicenseEntity',
                'preferred_choices' => function (LicenseEntity $license) {
                    return !$license->isOutdated();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->orderBy('l.title', 'ASC')
                        ->where('l.enabledForUpload = 1');
                        // @todo Move to the actual uploadable file types.
                },
                'placeholder' => $this->__('Unknown'),
                'property' => 'title',
                'attr' => isset($options['hiddenFields']) && in_array('license', $options['hiddenFields']) ? $hiddenAttr : [],
                'label_attr' => isset($options['hiddenFields']) && in_array('license', $options['hiddenFields']) ? $hiddenAttr : []
            ])
            ->add('author', isset($options['hiddenFields']) && in_array('author', $options['hiddenFields']) ? 'hidden' : 'text', [
                'label' => $this->__('Author'),
                'required' => false,
                'empty_data' => null
            ])
            ->add('authorUrl', isset($options['hiddenFields']) && in_array('authorUrl', $options['hiddenFields']) ? 'hidden' : 'url', [
                'label' => $this->__('Author URL'),
                'required' => false,
                'empty_data' => null
            ])
            ->add('authorAvatarUrl', isset($options['hiddenFields']) && in_array('authorAvatarUrl', $options['hiddenFields']) ? 'hidden' : 'url', [
                'label' => $this->__('Author Avatar URL'),
                'required' => false,
                'empty_data' => null
            ])
            ->add('mediaType', 'hidden', [
                'mapped' => false
            ])
            ->add('extraData', 'hidden')
        ;

        $builder->get('extraData')->addModelTransformer(new ArrayToJsonTransformer());
    }
}
