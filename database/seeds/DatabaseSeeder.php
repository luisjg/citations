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

        // un-comment this to replace the keys table and its associated
        // pivot table values with three new API keys
        //$this->call('KeysTableSeeder');
    }
}
