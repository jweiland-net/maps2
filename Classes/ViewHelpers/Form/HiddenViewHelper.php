<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\ViewHelpers\Form;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Renders an :html:`<input type="hidden" ...>` tag.
 *
 * This VH is a copy of the original f:hidden VH with ONE difference:
 * It adds a required __identity flag for property
 * Many other f:form.* VHs do so, but f:hidden not. Don't know why.
 *
 * Remove this VH, if this is resolved: https://forge.typo3.org/issues/90331
 */
class HiddenViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'input';

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
    }

    /**
     * Renders the hidden field.
     *
     * @return string
     */
    public function render()
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
