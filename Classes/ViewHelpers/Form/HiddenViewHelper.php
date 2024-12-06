<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers\Form;

use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * Renders an :html:`<input type="hidden" ...>` tag.
 * This VH is a copy of the original f:hidden VH with ONE difference:
 * It adds a required __identity flag for property
 * Many other f:form.* VHs do so, but f:hidden not. Don't know why.
 * Remove this VH, if this is resolved: https://forge.typo3.org/issues/90331
 */
class HiddenViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    public function initializeArguments(): void
    {
        parent::initializeArguments();
    }

    /**
     * Renders the hidden field.
     */
    public function render(): string
    {
        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);
        $this->setRespectSubmittedDataValue(true);

        $this->tag->addAttribute('type', 'hidden');
        $this->tag->addAttribute('name', $name);
        $this->tag->addAttribute('value', $this->getValueAttribute());

        // Add required __identify field
        $this->addAdditionalIdentityPropertiesIfNeeded();

        return $this->tag->render();
    }
}
