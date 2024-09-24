<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Tests\Unit\Stub\TooltipFormExtensionStub;
use Oro\Bundle\GoogleTagManagerBundle\Entity\GoogleTagManagerSettings;
use Oro\Bundle\GoogleTagManagerBundle\Form\Type\GoogleTagManagerSettingsType;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GoogleTagManagerSettingsTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /** @var GoogleTagManagerSettingsType */
    private $formType;

    #[\Override]
    protected function setUp(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->any())
            ->method('getSettingsEntityFQCN')
            ->willReturn(GoogleTagManagerSettings::class);

        $this->formType = new GoogleTagManagerSettingsType($transport);

        parent::setUp();
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(?array $defaultData, array $submittedData, array $expectedData): void
    {
        /** @var GoogleTagManagerSettings|null $defaultEntity */
        $defaultEntity = $defaultData ? $this->getEntity(GoogleTagManagerSettings::class, $defaultData) : null;

        $form = $this->factory->create(GoogleTagManagerSettingsType::class, $defaultEntity);

        $this->assertFormContainsField('containerId', $form);
        $this->assertEquals($defaultEntity, $form->getData());

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
        $optionResolver = $this->createMock(OptionsResolver::class);
        $optionResolver->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => GoogleTagManagerSettings::class]);

        $this->formType->configureOptions($optionResolver);
    }

    #[\Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    $this->formType,
                ],
                [
                    FormType::class => [new TooltipFormExtensionStub($this)]
                ]
            ),
            $this->getValidatorExtension(true)
        ];
    }
}
