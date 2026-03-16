<?php

declare(strict_types=1);

namespace Bunnivo\Soda;

use Bunnivo\Soda\Quality\RuleSections;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuleSections::class)]
#[Small]
final class RuleSectionsTest extends TestCase
{
    public function testSectionNames(): void
    {
        $names = RuleSections::sectionNames();

        $this->assertSame(['structural', 'complexity', 'breathing'], $names);
    }

    public function testSectionsContainExpectedRules(): void
    {
        $sections = RuleSections::sections();

        $this->assertArrayHasKey('structural', $sections);
        $this->assertContains('max_method_length', $sections['structural']);
        $this->assertContains('max_class_length', $sections['structural']);
        $this->assertContains('max_classes_per_project', $sections['structural']);

        $this->assertArrayHasKey('complexity', $sections);
        $this->assertContains('max_cyclomatic_complexity', $sections['complexity']);
        $this->assertContains('max_control_nesting', $sections['complexity']);

        $this->assertArrayHasKey('breathing', $sections);
        $this->assertContains('min_code_breathing_score', $sections['breathing']);
        $this->assertContains('min_code_oxygen_level', $sections['breathing']);
    }

    public function testRuleToSectionMapsAllRules(): void
    {
        $map = RuleSections::ruleToSection();

        $this->assertSame('structural', $map['max_method_length']);
        $this->assertSame('complexity', $map['max_cyclomatic_complexity']);
        $this->assertSame('breathing', $map['min_code_breathing_score']);
    }
}
