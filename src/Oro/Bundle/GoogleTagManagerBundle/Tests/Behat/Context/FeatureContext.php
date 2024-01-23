<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Behat\Context;

use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\Configuration;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class FeatureContext extends OroFeatureContext
{
    private const CHANNEL_TYPE = 'oro_google_tag_manager';

    /**
     * Example: Given I enable GTM integration
     *
     * @Given /^(?:|I )enable GTM integration$/
     */
    public function enableGTMIntegration(): void
    {
        $container = $this->getAppContainer();

        /** @var Channel $channel */
        $channel = $container->get('oro_entity.doctrine_helper')
            ->getEntityManagerForClass(Channel::class)
            ->getRepository(Channel::class)
            ->findOneBy(['type' => self::CHANNEL_TYPE]);

        $configManager = $container->get('oro_config.global');
        $configManager->set(Configuration::getConfigKeyByName('integration'), $channel->getId());
        $configManager->flush();
    }

    /**
     * Example: Given do not change page on link click
     *
     * @Given /^do not change page on link click$/
     */
    public function preventClicks(): void
    {
        $this->getSession()->evaluateScript(
            'window.document.addEventListener("click", function (e) { e.preventDefault(); });'
        );
    }

    /**
     * Data layer must contain minimum one expected message.
     * Note: the indexed array is compared without checking the index by default.
     *
     * Example: Then GTM data layer must contain the following message:
     *            """
     *            {
     *              "visitorType": "Visitor"
     *            }
     *            """
     *
     * @Then /^GTM data layer must contain the following message:$/
     */
    public function gtmDataLayerMustContainTheFollowingMessage(string $expected): void
    {
        $expectedMessage = $this->messageNormalization($expected);
        $dataLayer = $this->getDataLayer();
        foreach ($dataLayer as $actual) {
            if ($this->compareMessages($expectedMessage, $actual)) {
                return;
            }
        }

        self::fail(
            sprintf(
                'The expected message is not present in the data layer %s . Current messages are: %s',
                json_encode($expectedMessage, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
                json_encode($dataLayer, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
            )
        );
    }

    /**
     * Data layer must not contain expected message.
     * Note: the indexed array is compared without checking the index by default.
     *
     * Example: Then GTM data layer must not contain the following message:
     *            """
     *            {
     *              "visitorType": "Visitor"
     *            }
     *            """
     *
     * @Then /^GTM data layer must not contain the following message:$/
     */
    public function gtmDataLayerMustNotContainTheFollowingMessage(string $expected): void
    {
        $expectedMessage = $this->messageNormalization($expected);
        $dataLayer = $this->getDataLayer();
        foreach ($dataLayer as $actual) {
            if ($this->compareMessages($expectedMessage, $actual)) {
                sprintf(
                    'The expected message must not be present in the data layer. Current messages are: %s',
                    json_encode($dataLayer, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
                );
            }
        }
    }

    /**
     * Example: Then last message in the GTM data layer should be:
     *            """
     *            {
     *              "event": "view_promotion",
     *              "ecommerce": {
     *                "items": [{"creative_name": "home-page-slider", "item_name": "Lorem ipsum", "index": 0}]
     *              }
     *            }
     *            """
     *
     * @Then /^last message in the GTM data layer should be:$/
     */
    public function lastMessageInDataLayerShouldBe(string $expected): void
    {
        $expectedMessage = $this->messageNormalization($expected);

        $lastMessage = null;
        $compareResult = $this->spin(function () use ($expectedMessage, &$lastMessage) {
            try {
                $dataLayer = $this->getDataLayer();
            } catch (\Throwable $e) {
                return $e;
            }

            $lastMessage = \end($dataLayer);
            return $this->compareMessages($expectedMessage, $lastMessage);
        }, 20);

        if ($compareResult instanceof \Throwable) {
            throw $compareResult;
        }

        if (!$compareResult) {
            self::fail(
                'The last message in the data layer is different from the expected. Last message is '
                . \json_encode($lastMessage, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
            );
        }
    }

    private function compareMessages(array $expected, array $actual): bool
    {
        if (\count($expected) !== \count($actual)) {
            return false;
        }

        $strategy = $this->getComparisonStrategy($expected);

        foreach ($expected as $key => $value) {
            $equalKey = $strategy($actual, $value, $key);
            if ($equalKey === false) {
                return false;
            }

            unset($actual[$equalKey]);
        }

        return true;
    }

    /**
     * Chooses a comparison strategy for arrays based on the type of expected array: associative or indexed.
     */
    private function getComparisonStrategy(array $expected): callable
    {
        return \is_int(key($expected))
            ? [$this, 'compareStrategyIndexed']
            : [$this, 'compareStrategyAssociative'];
    }

    /**
     * Compares message without index check.
     *
     * @see getComparisonStrategy
     */
    private function compareStrategyIndexed(array $actual, mixed $expected): string|bool
    {
        foreach ($actual as $actualKey => $actualValue) {
            if (!\is_numeric($actualKey)) {
                return false;
            }

            if (\is_array($expected) && \is_array($actualValue)) {
                if ($this->compareMessages($expected, $actualValue)) {
                    return $actualKey;
                }
            } elseif ($expected === $actual) {
                return $actualKey;
            }
        }

        return false;
    }

    /**
     * Compares messages with index check.
     *
     * @see getComparisonStrategy
     */
    private function compareStrategyAssociative(array $actual, mixed $expected, string $key): string|bool
    {
        if (!\array_key_exists($key, $actual)) {
            return false;
        }

        if (\is_array($expected)) {
            if (\is_array($actual[$key]) && $this->compareMessages($expected, $actual[$key])) {
                return $key;
            }

            return false;
        }

        return $expected === $actual[$key];
    }

    private function messageNormalization(string|array $message): array
    {
        if (\is_string($message)) {
            $message = \json_decode($message, true, 512, JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($message, 'GTM data layer message must be a correct json or array');

        return $message;
    }

    /**
     * Gets current data layer messages.
     */
    private function getDataLayer(int $try = 1): array
    {
        if ($try >= 5) {
            self::fail(
                'Cannot get the stable data layer state: window.dataLayer keeps changing after 5 tries.'
                . ' Looks like JS is constantly pushing new messages to data layer.'
            );
        }

        $dataLayer = $this->spin(function () {
            /** @var array $dataLayer */
            $dataLayer = $this->getSession()->evaluateScript(
                <<<JS
                (function () {
                    if (window.dataLayer instanceof Array) {
                        return window.dataLayer;
                    }
                    
                    return null;
                })();
JS
            );
            if (!is_array($dataLayer)) {
                return false;
            }

            return $dataLayer;
        }, 10);

        self::assertIsArray($dataLayer, 'GTM integration is not enabled');

        // Tries to detect a change in data layer during 1 second.
        $isDataLayerChanged = $this->spin(function () use ($dataLayer) {
            /** @var array $dataLayer */
            $dataLayer2 = $this->getSession()->evaluateScript('return window.dataLayer;');

            return $dataLayer !== $dataLayer2;
        }, 1);

        if ($isDataLayerChanged) {
            // Data layer is changed. It means that not all messages are pushed. Tries again to get
            // the stable data layer.
            return $this->getDataLayer(++$try);
        }

        return $dataLayer;
    }
}
