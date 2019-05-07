<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit;

use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass\CollectorCompilerPass;
use Oro\Bundle\GoogleTagManagerBundle\DependencyInjection\CompilerPass\PageTypeGuesserCompilerPass;
use Oro\Bundle\GoogleTagManagerBundle\OroGoogleTagManagerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroGoogleTagManagerBundleTest extends \PHPUnit\Framework\TestCase
{
    public function testBuild(): void
    {
        /** @var ContainerBuilder|\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(CollectorCompilerPass::class));
        $container->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(PageTypeGuesserCompilerPass::class));

        $bundle = new OroGoogleTagManagerBundle();
        $bundle->build($container);
    }
}
