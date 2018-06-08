<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$this->call('PermissionsTableSeeder');
        $this->call('ScopesTableSeeder');
        $this->call('PermissionScopeTableSeeder');

        // un-comment this to generate three API keys
        $this->call('KeysTableSeeder');
    }
}
