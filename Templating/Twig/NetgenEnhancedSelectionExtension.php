<?php

namespace Netgen\Bundle\EnhancedSelectionBundle\Templating\Twig;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\API\Repository\Values\Content\Content;
use Twig_SimpleFunction;
use Twig_Extension;

class NetgenEnhancedSelectionExtension extends Twig_Extension
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var \eZ\Publish\Core\Helper\TranslationHelper
     */
    protected $translationHelper;

    /**
     * NetgenEnhancedSelectionExtension constructor.
     *
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\Core\Helper\TranslationHelper $translationHelper
     */
    public function __construct(ContentTypeService $contentTypeService, TranslationHelper $translationHelper)
    {
        $this->contentTypeService = $contentTypeService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'netgen_enhanced_selection_name',
                array($this, 'getSelectionName')
            ),
        );
    }

    /**
     * Returns selection names
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string $fieldDefIdentifier
     * @param null|string $selectionIdentifier
     *
     * @return array|string
     */
    public function getSelectionName(Content $content, $fieldDefIdentifier, $selectionIdentifier = null)
    {
        $names = array();

        if (empty($selectionIdentifier)) {
            $field = $this->translationHelper->getTranslatedField($content, $fieldDefIdentifier);
            $identifiers = $field->value->identifiers;
        }

        try {
            $contentType = $this->contentTypeService->loadContentType(
                $content->contentInfo->contentTypeId
            );
        } catch (NotFoundException $e) {
            return $names;
        }


        $fieldDefinitions = $contentType->fieldDefinitions;

        foreach ($fieldDefinitions as $fieldDefinition) {
            if ($fieldDefinition->identifier === $fieldDefIdentifier) {
                foreach ($fieldDefinition->fieldSettings['options'] as $option) {
                    if (!is_null($selectionIdentifier) && $option['identifier'] === $selectionIdentifier) {
                        return $option['name'];
                    } else if (in_array($option['identifier'], $identifiers)) {
                        $names[$option['identifier']] = $option['name'];
                    }
                }
            }
        }

        return $names;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'netgen_enhanced_selection';
    }
}
