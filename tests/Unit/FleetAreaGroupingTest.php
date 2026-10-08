<?php

namespace Tests\Unit;

use App\Services\FleetAreaGrouping;
use PHPUnit\Framework\TestCase;

class FleetAreaGroupingTest extends TestCase
{
    private function cluster(int $id, string $name, ?string $area): array
    {
        return ['id' => $id, 'name' => $name, 'area' => $area, 'sort_order' => $id];
    }

    private function row(int $id, string $group, string $loader): array
    {
        return ['id' => $id, 'group_name' => $group, 'loader_id' => $loader];
    }

    private function names(array $areas): array
    {
        return array_map(fn ($a) => [$a['name'], array_column($a['clusters'], 'name')], $areas);
    }

    public function test_areas_follow_first_added_cluster_and_clusters_follow_insertion_order(): void
    {
        $clusters = [
            $this->cluster(1, 'OB IPPKH', 'Timur'), $this->cluster(2, 'COAL', 'Barat'),
            $this->cluster(3, 'SOIL', ' timur '), $this->cluster(4, 'EWT', 'BARAT'),
        ];
        $rows = [$this->row(10, 'SOIL', 'E1'), $this->row(11, 'EWT', 'E2'), $this->row(12, 'COAL', 'E3'), $this->row(13, 'OB IPPKH', 'E4')];

        $areas = FleetAreaGrouping::group($clusters, $rows);

        $this->assertSame([['Timur', ['OB IPPKH', 'SOIL']], ['Barat', ['COAL', 'EWT']]], $this->names($areas));
    }

    public function test_legacy_and_arealess_clusters_go_to_last_unassigned_band_ordered_by_first_row(): void
    {
        $clusters = [$this->cluster(1, 'NO AREA', null), $this->cluster(2, 'OB', 'Timur')];
        $rows = [$this->row(30, 'OLD B', 'E1'), $this->row(20, 'OLD A', 'E2'), $this->row(25, 'NO AREA', 'E3'), $this->row(40, 'OB', 'E4')];

        $areas = FleetAreaGrouping::group($clusters, $rows);

        $this->assertSame([['Timur', ['OB']], [null, ['OLD A', 'NO AREA', 'OLD B']]], $this->names($areas));
    }

    public function test_empty_clusters_and_breakdown_group_are_excluded_unless_requested(): void
    {
        $clusters = [$this->cluster(1, 'EMPTY', 'Timur'), $this->cluster(2, 'OB', 'Timur')];
        $rows = [$this->row(1, 'OB', 'E1'), $this->row(2, 'BREAKDOWN / SERVICE', 'SERVICE & BD'), $this->row(3, 'OB', '')];

        $this->assertSame([['Timur', ['OB']]], $this->names(FleetAreaGrouping::group($clusters, $rows)));
        $this->assertSame([['Timur', ['EMPTY', 'OB']]], $this->names(FleetAreaGrouping::group($clusters, $rows, true)));
        $this->assertSame(1, FleetAreaGrouping::group($clusters, $rows)[0]['clusters'][0]['loader_count']);
    }

    public function test_cluster_name_matches_group_name_case_insensitively(): void
    {
        $areas = FleetAreaGrouping::group([$this->cluster(1, 'Coal Getting', 'Barat')], [$this->row(1, 'COAL GETTING', 'E1')]);

        $this->assertSame([['Barat', ['Coal Getting']]], $this->names($areas));
    }
}
