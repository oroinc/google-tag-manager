<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Inherits Transport Integration.
 *
 * @ORM\Entity()
 */
class GoogleTagManagerSettings extends Transport
{
    /**
     * @var string
     *
     * @ORM\Column(name="gtm_container_id", type="string", length=30)
     */
    private $containerId;

    /**
     * @var ParameterBag
     */
    private $settings;

    public function getContainerId(): ?string
    {
        return $this->containerId;
    }

    public function setContainerId(?string $containerId): GoogleTagManagerSettings
    {
        $this->containerId = $containerId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag(): ParameterBag
    {
        if (!$this->settings) {
            $this->settings = new ParameterBag(
                [
                    'container_id' => $this->getContainerId(),
                ]
            );
        }

        return $this->settings;
    }
}
