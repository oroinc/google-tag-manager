<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Behat\Context;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class FeatureContext extends OroFeatureContext implements KernelAwareContext
{
    use KernelDictionary;

    private const CHANNEL_TYPE = 'oro_google_tag_manager';

    /** @var int */
    private $batchSize;

    /**
     * Example: Given I enable GTM integration
     *
     * @Given /^(?:|I )enable GTM integration$/
     */
    public function enableGTMIntegration(): void
    {
        $container = $this->getContainer();
        
        /** @var Channel $channel */
        $channel = $container->get('oro_entity.doctrine_helper')
            ->getEntityManagerForClass(Channel::class)
            ->getRepository(Channel::class)
            ->findOneBy(['type' => self::CHANNEL_TYPE]);
        
        $configManager = $container->get('oro_config.global');
        $configManager->set('oro_google_tag_manager.integration', $channel->getId());
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
     * Data layer must must contain minimum one expected message.
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
     * @param string $expected
     */
    public function gtmDataLayerMustContainTheFollowingMessage(string $expected): void
    {
        $expectedMessage = $this->messageNormalization($expected);

        foreach ($this->getDataLayer() as $actual) {
            if ($this->compareMessages($expectedMessage, $actual)) {
                return;
            }
        }

        self::fail('Message not find in data layer');
    }

    /**
     * Data layer must must not contain expected message.
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
     * @param string $expected
     */
    public function gtmDataLayerMustNotContainTheFollowingMessage(string $expected): void
    {
        $expectedMessage = $this->messageNormalization($expected);

        foreach ($this->getDataLayer() as $actual) {
            if ($this->compareMessages($expectedMessage, $actual)) {
                self::fail('Message find in data layer');
            }
        }
    }

    /**
     * Example: Then last message in the GTM data layer should be:
     *            """
     *            {
     *              event: 'promotionImpression',
     *              ecommerce: {
     *                promoView: {promotions: [{name: 'Lorem ipsum', creative: 'home-page-slider', position: 0}]}
     *              }
     *            }
     *            """
     *
     * @Then /^last message in the GTM data layer should be:$/
     * @param string $expected
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
                . \json_encode($lastMessage, JSON_PRETTY_PRINT)
            );
        }
    }

    /**
     * @codingStandardsIgnoreStart
     *
     * @Then /^GTM data layer must contain checkout events for step "(?P<step>(?:[^"]|\\")*)" with (?P<quantity>\d+) products$/
     *
     * @codingStandardsIgnoreEnd
     *
     * @param string $step
     * @param int $productQuantity
     */
    public function gtmDataLayerMustContainCheckoutEvents(string $step, int $productQuantity): void
    {
        $foundProducts = [];
        foreach ($this->getDataLayer() as $message) {
            if (isset($message['event'], $message['ecommerce']['checkout']['actionField']['option'])
                && $message['event'] === 'checkout'
                && $message['ecommerce']['checkout']['actionField']['option'] === $step
            ) {
                $this->assertFoundProducts($message, 'checkout', $foundProducts);
            }
        }

        self::assertCount($productQuantity, $foundProducts);
    }

    /**
     * @Then /^GTM data layer must contain purchase events with (?P<quantity>\d+) products$/
     * @param int $productQuantity
     */
    public function gtmDataLayerMustContainPurchaseEvents(int $productQuantity): void
    {
        $foundProducts = [];
        $id = null;
        $found = ['revenue' => false, 'tax' => false, 'shipping' => false, 'affiliation' => false];

        foreach ($this->getDataLayer() as $message) {
            if (isset($message['event']) && $message['event'] === 'purchase') {
                self::assertTrue(isset($message['ecommerce']['purchase']['actionField']['id']));
                if ($id === null) {
                    $id = $message['ecommerce']['purchase']['actionField']['id'];
                } else {
                    self::assertSame($id, $message['ecommerce']['purchase']['actionField']['id']);
                }

                // Every of action fields must be specified in only one of chunked messages
                $intersected = array_intersect_key($found, $message['ecommerce']['purchase']['actionField']);
                self::assertTrue(
                    !array_filter($intersected),
                    sprintf('Duplicate data in purchase action fields: %s', implode(', ', array_keys($intersected)))
                );
                $found = array_merge($found, array_fill_keys(array_keys($intersected), true));

                $this->assertFoundProducts($message, 'purchase', $foundProducts);
            }
        }

        self::assertCount($productQuantity, $foundProducts);
        self::assertCount(
            count($found),
            array_filter($found),
            sprintf(
                'Not all required purchase action fields are filled: %s',
                implode(', ', array_keys(array_diff_key($found, array_filter($found))))
            )
        );
    }

    /**
     * @Then /^GTM data layer must contain (?P<event>addToCart|removeFromCart) events with (?P<quantity>\d+) products$/
     * @param string $event
     * @param int $productQuantity
     */
    public function gtmDataLayerMustContainShoppingListEvents(string $event, int $productQuantity): void
    {
        $lastError = null;
        $productKey = $event === 'addToCart' ? 'add' : 'remove';
        $foundMessages = $this->spin(function () use ($event, $productKey, $productQuantity, &$lastError) {
            try {
                $foundMessages = 0;
                $foundProducts = [];
                foreach ($this->getDataLayer() as $message) {
                    if (isset($message['event']) && $message['event'] === $event) {
                        $foundMessages++;
                        $this->assertFoundProducts($message, $productKey, $foundProducts);
                    }
                }

                self::assertCount($productQuantity, $foundProducts);
            } catch (\Exception $exception) {
                $lastError = $exception;
                return false;
            }

            $lastError = null;
            return $foundMessages;
        }, 10);

        if ($lastError && $lastError instanceof \Throwable) {
            throw $lastError;
        }

        self::assertSame(
            (int)ceil($productQuantity / $this->getBatchSize()),
            $foundMessages,
            'Invalid batching: found not excepted message quantity'
        );
    }

    /**
     * @param array $message
     * @param string $key
     * @param array $foundProducts
     */
    private function assertFoundProducts(array $message, string $key, array &$foundProducts): void
    {
        self::assertTrue(
            isset($message['ecommerce'][$key]['products'])
            && \is_array($message['ecommerce'][$key]['products'])
            && count($message['ecommerce'][$key]['products'])
            && count($message['ecommerce'][$key]['products']) <= $this->getBatchSize()
        );

        foreach ($message['ecommerce'][$key]['products'] as $product) {
            self::assertArrayHasKey('id', $product);
            self::assertArrayNotHasKey($product['id'], $foundProducts);

            $foundProducts[$product['id']] = null;
        }
    }

    /**
     * @param array $expected
     * @param array $actual
     * @return bool
     */
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
     * Choose a comparison strategy for arrays based on the type of expected array: associative or indexed
     *
     * @param array $expected
     * @return callable
     */
    private function getComparisonStrategy(array $expected): callable
    {
        return \is_int(key($expected))
            ? [$this, 'compareStrategyIndexed']
            : [$this, 'compareStrategyAssociative'];
    }

    /**
     * Compare message without index check
     *
     * @param $expected
     * @param array $actual
     * @return bool
     *
     * @see getComparisonStrategy
     */
    private function compareStrategyIndexed(array $actual, $expected)
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
     * Compare messages with index check
     *
     * @param array $actual
     * @param mixed $expected
     * @param string $key
     * @return bool|string|int key
     *
     * @see getComparisonStrategy
     */
    private function compareStrategyAssociative(array $actual, $expected, $key)
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

    /**
     * @param string|array $message
     * @return array
     */
    private function messageNormalization($message): array
    {
        if (\is_string($message)) {
            $message = \json_decode($message, true);
        }

        self::assertInternalType('array', $message, 'GTM data layer message must be a correct json or array');

        return $message;
    }

    /**
     * Get current data layer events list
     * @return array
     */
    private function getDataLayer(): array
    {
        /** @var array $currentDataLayer */
        $currentDataLayer = $this->getSession()->evaluateScript(
            'return typeof window.dataLayer != "undefined" && window.dataLayer;'
        );

        self::assertInternalType('array', $currentDataLayer, 'GTM integration is not enabled');
        return $currentDataLayer;
    }

    /**
     * @return int
     */
    private function getBatchSize(): int
    {
        if (!$this->batchSize) {
            $this->batchSize = (int) $this->getContainer()->getParameter('oro_google_tag_manager.products.batch_size');
        }

        return $this->batchSize;
    }
}
