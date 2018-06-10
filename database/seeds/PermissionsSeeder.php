<?php

use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    use \Illuminate\Foundation\Bus\DispatchesJobs;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        (new \App\Models\Permission())->insert([
            [
                'entity_type_id'=>7,
                'entity_id'=>\App\Models\Entity::USERS,
                'permission_mask'=>0b1101,
            ],
            [
                'entity_type_id'=>100,
                'entity_id'=>\App\Models\Entity::USERS,
                'permission_mask'=>0b111,
            ],
            [
                'entity_type_id'=>100,
                'entity_id'=>\App\Models\Entity::GROUPS,
                'permission_mask'=>0b1101,
            ],
//            [
//                'entity_type_id'=>100,
//                'entity_id'=>\App\Models\Entity::GROUP_MEMBERS,
//                'permission_mask'=>0b1010,
//            ],
//            [
//                'entity_type_id'=>101,
//                'entity_id'=>\App\Models\Entity::GROUP_MEMBERS,
//                'permission_mask'=>0b1010,
//            ],
        ]);
        $this->dispatch(new \App\Jobs\UpdatePermissions);
    }
}
