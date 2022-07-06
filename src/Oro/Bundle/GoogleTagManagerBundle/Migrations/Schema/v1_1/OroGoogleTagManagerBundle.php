<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ConfigBundle\Migration\DeleteConfigQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Deletes the rows of "oro_google_tag_manager.enabled_data_collection_types" system config option
 * because it is not available on UI anymore and universal_analytics data collection type is gone.
 */
class OroGoogleTagManagerBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $queries->addQuery(new DeleteConfigQuery('enabled_data_collection_types', 'oro_google_tag_manager'));
    }
}
