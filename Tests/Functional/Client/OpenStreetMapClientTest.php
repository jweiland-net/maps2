<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Tests\Functional\Client;

use JWeiland\Maps2\Client\OpenStreetMapClient;
use JWeiland\Maps2\Client\Request\OpenStreetMap\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MessageHelper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test Open Street Map class
 */
class OpenStreetMapClientTest extends FunctionalTestCase
{
    protected OpenStreetMapClient $subject;

    protected ExtConf $extConf;

    /**
     * @var MessageHelper|MockObject
     */
    protected $messageHelperMock;

    /**
     * @var RequestFactory|MockObject
     */
    protected $requestFactoryMock;

    protected array $testExtensionsToLoad = [
        'jweiland/maps2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);
        $this->messageHelperMock = $this->createMock(MessageHelper::class);
        $this->requestFactoryMock = $this->createMock(RequestFactory::class);

        $this->subject = new OpenStreetMapClient(
            $this->messageHelperMock,
            $this->requestFactoryMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf,
            $this->messageHelperMock,
            $this->requestFactoryMock,
        );

        parent::tearDown();
    }

    #[Test]
    public function processRequestWithEmptyUriAddsFlashMessage(): void
    {
        $geocodeRequest = new GeocodeRequest($this->extConf);
        $geocodeRequest->setUri('');

        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                'URI is empty or contains invalid chars. URI: ',
                'Invalid request URI',
                ContextualFeedbackSeverity::ERROR,
            );

        self::assertSame(
            [],
            $this->subject->processRequest($geocodeRequest),
        );
    }

    #[Test]
    public function processRequestWithInvalidRequestAddsFlashMessage(): void
    {
        $geocodeRequest = new GeocodeRequest($this->extConf);
        $geocodeRequest->setUri('https://www.jweiländ.net');

        $this->messageHelperMock
            ->expects(self::atLeastOnce())
            ->method('addFlashMessage')
            ->with(
                'URI is empty or contains invalid chars. URI: https://www.jweiländ.net',
                'Invalid request URI',
                ContextualFeedbackSeverity::ERROR,
            );

        self::assertSame(
            [],
            $this->subject->processRequest($geocodeRequest),
        );
    }
}
