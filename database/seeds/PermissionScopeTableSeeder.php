<?php

use Illuminate\Database\Seeder;
use App\ApiPermission;

class PermissionScopeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $table = 'permission_scope';

        DB::table($table)->delete();
        DB::table($table)->insert([
            // "import-scopus" scope includes all Scopus import permissions
        	[
        		'scope' => 'import-scopus',
        		'permission' => 'citations.import.orcid',
        	],
            [
                'scope' => 'import-scopus',
                'permission' => 'citations.import.author',
            ],

            // "manipulate" scope includes all citation manipulation permissions
            [
                'scope' => 'manipulate',
                'permission' => 'citations.store',
            ],
            [
                'scope' => 'manipulate',
                'permission' => 'citations.update',
            ],
            [
                'scope' => 'manipulate',
                'permission' => 'citations.destroy',
            ],
            [
                'scope' => 'manipulate',
                'permission' => 'citations.members.store',
            ],
            [
                'scope' => 'manipulate',
                'permission' => 'citations.members.destroy',
            ],
        ]);
    }
}
