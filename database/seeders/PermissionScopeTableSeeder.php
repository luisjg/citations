<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\ApiPermission;

class PermissionScopeTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $table = 'permission_scope';

        DB::table($table)->delete();
        DB::table($table)->insert([
            // "import.scopus" scope includes all Scopus import permissions
        	[
        		'scope' => 'citations.import.scopus',
        		'permission' => 'citations.import.orcid',
        	],
            [
                'scope' => 'citations.import.scopus',
                'permission' => 'citations.import.author',
            ],

            // "citations.manipulate" scope includes all citation manipulation permissions
            [
                'scope' => 'citations.manipulate',
                'permission' => 'citations.store',
            ],
            [
                'scope' => 'citations.manipulate',
                'permission' => 'citations.update',
            ],
            [
                'scope' => 'citations.manipulate',
                'permission' => 'citations.destroy',
            ],
            [
                'scope' => 'citations.manipulate',
                'permission' => 'citations.members.store',
            ],
            [
                'scope' => 'citations.manipulate',
                'permission' => 'citations.members.destroy',
            ],
        ]);
    }
}
