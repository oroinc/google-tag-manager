<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FormBundle\Form\Extension\TooltipFormExtension;
use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\GoogleTagManagerBundle\Form\Type\GoogleTagManagerSettingsType;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\TranslationBundle\Translation\Translator;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GoogleTagManagerSettingsTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /** @var TransportInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $transport;

    /** @var GoogleTagManagerSettingsType */
    private $formType;

    protected function setUp(): void
    {
        $this->transport = $this->createMock(TransportInterface::class);
        $this->transport->expects($this->any())
            ->method('getSettingsEntityFQCN')
            ->willReturn(GoogleTagManagerSettings::class);

        $this->formType = new GoogleTagManagerSettingsType($this->transport);

        parent::setUp();
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(?array $defaultData, array $submittedData, array $expectedData): void
    {
        /** @var GoogleTagManagerSettings $defaultData */
        $defaultData = $defaultData ? $this->getEntity(GoogleTagManagerSettings::class, $defaultData) : null;

        $form = $this->factory->create(GoogleTagManagerSettingsType::class, $defaultData);

        $this->assertFormContainsField('containerId', $form);
        $this->assertEquals($defaultData, $form->getData());

        $form->submit($submittedData);

        $this->assertTrue($form->isSubmitted());
        $this->assertTrue($form->isSynchronized());
        $this->assertFormIsValid($form);
        $this->assertEquals($this->getEntity(GoogleTagManagerSettings::class, $expectedData), $form->getData());
    }

    public function submitDataProvider(): array
    {
        return [
            'no default data' => [
                'defaultData' => null,
                'submittedData' => ['containerId' => 'container-id-new'],
                'expectedData' => ['containerId' => 'container-id-new']
            ],
            'update default data' => [
                'defaultData' => ['containerId' => 'container-id-old'],
                'submittedData' => ['containerId' => 'container-id-new'],
                'expectedData' => ['containerId' => 'container-id-new']
            ]
        ];
    }

    public function testConfigureOptions(): void
    {
        /** @var OptionsResolver|\PHPUnit\Framework\MockObject\MockObject $optionResolver */
        $optionResolver = $this->createMock(OptionsResolver::class);
        $optionResolver->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => GoogleTagManagerSettings::class]);

        $this->formType->configureOptions($optionResolver);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions(): array
    {
        /** @var ConfigProvider $entityConfigProvider */
        $entityConfigProvider = $this->createMock(ConfigProvider::class);

        /** @var Translator $translator */
        $translator = $this->createMock(Translator::class);

        return [
            new PreloadedExtension(
                [
                    GoogleTagManagerSettingsType::class => $this->formType,
                ],
                [
                    FormType::class => [
                        new TooltipFormExtension($entityConfigProvider, $translator),
                    ],
                ]
            ),
            $this->getValidatorExtension(true)
        ];
    }
}
