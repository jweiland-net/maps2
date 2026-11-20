<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Form\PoiPosition;

use JWeiland\Maps2\Form\Validator\PoiPositionValidator;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Form\Domain\Factory\ArrayFormFactory;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Tests the type definition of the form element "Maps2PoiPosition"
 */
class PoiPositionFormDefinitionTest extends FunctionalTestCase
{
    private const FORM_DEFINITION = 'EXT:maps2/Tests/Functional/Fixtures/Form/PoiPosition.form.yaml';

    protected array $coreExtensionsToLoad = [
        'form',
    ];

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        parent::tearDown();
    }

    #[Test]
    public function elementHasValidatorOfTypeDefinitionAndForm(): void
    {
        $validatorClasses = array_map(
            static fn(object $validator): string => $validator::class,
            iterator_to_array($this->buildPositionElement()->getValidators(), false),
        );

        self::assertContains(PoiPositionValidator::class, $validatorClasses);
        self::assertContains(NotEmptyValidator::class, $validatorClasses);
    }

    #[Test]
    public function elementHasDefaultValueAndProperties(): void
    {
        $element = $this->buildPositionElement();

        self::assertSame(['latitude' => '48.1', 'longitude' => '9.2'], $element->getDefaultValue());
        self::assertTrue($element->isRequired());
        self::assertSame('My company', $element->getProperties()['markerTitle']);
        self::assertSame(12, $element->getProperties()['zoom']);
        self::assertSame('', $element->getProperties()['mapProvider']);
    }

    #[Test]
    public function validatorsOfElementRejectInvalidPosition(): void
    {
        $messages = [];
        foreach ($this->buildPositionElement()->getValidators() as $validator) {
            foreach ($validator->validate(['latitude' => '95', 'longitude' => '9'])->getErrors() as $error) {
                $messages[] = $error->getMessage();
            }
        }

        self::assertSame(['The latitude must be between -90 and 90.'], $messages);
    }

    private function buildPositionElement(): FormElementInterface
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->buildRequest();
        $this->get(ConfigurationManagerInterface::class)->setRequest($GLOBALS['TYPO3_REQUEST']);

        $formDefinition = $this->get(ArrayFormFactory::class)->build(
            Yaml::parseFile(GeneralUtility::getFileAbsFileName(self::FORM_DEFINITION)),
            'standard',
            $GLOBALS['TYPO3_REQUEST'],
        );

        return $formDefinition->getElementByIdentifier('position');
    }

    private function buildRequest(): ServerRequest
    {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([
            'plugin.' => [
                'tx_form.' => ['settings.' => ['yamlConfigurations.' => [
                    '10' => 'EXT:form/Configuration/Yaml/FormSetup.yaml',
                    '1763380200' => 'EXT:maps2/Configuration/Form/FormSetup.yaml',
                ]]],
            ],
        ]);

        return (new ServerRequest('https://www.example.com/'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.typoscript', $frontendTypoScript)
            ->withAttribute('language', new SiteLanguage(0, 'en_US.UTF-8', new Uri('https://www.example.com/'), []));
    }
}
