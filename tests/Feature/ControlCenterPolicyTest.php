<?php

namespace Tests\Feature;

use App\Support\ControlCenterPolicy;
use Tests\TestCase;

class ControlCenterPolicyTest extends TestCase
{
    private function policy(): array
    {
        return [
            'disabled_modules' => ['one-gmp', 'one-core'],
            'disabled_menus' => ['one-power' => ['Roster Personal']],
            'target_version' => 'v2.5.0',
        ];
    }

    private function labels(array $tree): array
    {
        return collect($tree)
            ->flatMap(fn (array $item) => array_merge([$item['label']], array_column($item['children'] ?? [], 'label')))
            ->all();
    }

    public function test_nothing_is_hidden_while_apply_config_is_off(): void
    {
        config(['services.control_center.apply_config' => false]);
        ControlCenterPolicy::store($this->policy());

        $this->assertTrue(ControlCenterPolicy::moduleEnabled('one-gmp'));
        $this->assertContains('Roster Personal', $this->labels(config('one_power_menu')));
        $this->assertContains('Roster Personal', $this->labels(ControlCenterPolicy::filterMenus('one-power', config('one_power_menu'))));
    }

    public function test_disabled_modules_and_menus_are_hidden_when_applied(): void
    {
        config(['services.control_center.apply_config' => true]);
        ControlCenterPolicy::store($this->policy());

        $this->assertFalse(ControlCenterPolicy::moduleEnabled('one-gmp'));
        $this->assertTrue(ControlCenterPolicy::moduleEnabled('one-power'));

        $labels = $this->labels(ControlCenterPolicy::filterMenus('one-power', config('one_power_menu')));
        $this->assertNotContains('Roster Personal', $labels);
        $this->assertContains('Roster Departement', $labels, 'The sibling stays visible.');
        $this->assertContains('Roster', $labels, 'The parent stays while a child remains.');
    }

    public function test_one_core_can_never_be_hidden(): void
    {
        config(['services.control_center.apply_config' => true]);
        ControlCenterPolicy::store($this->policy());

        $this->assertTrue(ControlCenterPolicy::moduleEnabled('one-core'));
    }

    public function test_sync_info_reports_what_was_received(): void
    {
        config(['services.control_center.apply_config' => true]);
        ControlCenterPolicy::store($this->policy());

        $info = ControlCenterPolicy::syncInfo();

        $this->assertTrue($info['apply']);
        $this->assertSame(2, $info['disabled_modules']);
        $this->assertSame(1, $info['disabled_menus']);
        $this->assertNotNull($info['synced_at']);
    }
}
