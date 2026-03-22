<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\Config\RuleSections;
use Bunnivo\Soda\Quality\QualityConfig;
use Bunnivo\Soda\Quality\Report\RuleMetadata;
use Bunnivo\Soda\Quality\RuleCatalog\RuleCatalog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuleCatalog::class)]
#[Small]
final class RuleCatalogTest extends TestCase
{
    public function testDefinitionsMatchDefaultConfigKeys(): void
    {
        $catalogIds = array_keys(RuleCatalog::definitions());
        $configKeys = array_keys(QualityConfig::default()->rules);

        sort($catalogIds);
        sort($configKeys);

        $this->assertSame($configKeys, $catalogIds);
    }

    public function testMetadataMatchesRuleMetadataDefault(): void
    {
        $fromCatalog = RuleCatalog::metadataMap();
        $fromFacade = RuleMetadata::default();

        foreach ($fromCatalog as $id => $row) {
            $this->assertSame($row['severity'], $fromFacade->severity($id), $id);
            $this->assertSame($row['label'], $fromFacade->label($id), $id);

            if (isset($row['comparison'])) {
                $this->assertSame($row['comparison'], $fromFacade->comparison($id), $id);
            }
        }
    }

    public function testSectionsOrderedMatchesRuleSections(): void
    {
        $this->assertSame(RuleSections::sections(), RuleCatalog::sectionsOrdered());
    }
}
