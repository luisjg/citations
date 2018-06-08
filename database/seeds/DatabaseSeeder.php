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
    }
}
