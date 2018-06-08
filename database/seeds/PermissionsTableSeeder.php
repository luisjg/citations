<?php

use Illuminate\Database\Seeder;
use App\ApiPermission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $table = (new ApiPermission())->getTable();

        DB::table($table)->delete();
        DB::table($table)->insert([
            // retrieval citations are public but included for completeness
            // in case we want to restrict public information to API keys
            // as well
        	[
        		'system_name' => 'colleges.citations.index',
        		'display_name' => 'Retrieve all citations for a college',
        	],
        	[
        		'system_name' => 'departments.citations.index',
        		'display_name' => 'Retrieve all citations for a department',
        	],
        	[
        		'system_name' => 'citations.index',
        		'display_name' => 'Retrieve all citations',
        	],
            [
                'system_name' => 'citations.show',
                'display_name' => 'Retrieve a single citation',
            ],

            // import permissions
            [
                'system_name' => 'citations.import.orcid',
                'display_name' => 'Import citations from Scopus via ORCID',
            ],
            [
                'system_name' => 'citations.import.author',
                'display_name' => 'Import citations from Scopus via Scopus author ID',
            ],

            // citation manipulation permissions
            [
                'system_name' => 'citations.store',
                'display_name' => 'Create a new citation',
            ],
            [
                'system_name' => 'citations.update',
                'display_name' => 'Modify an existing citation',
            ],
            [
                'system_name' => 'citations.destroy',
                'display_name' => 'Delete a citation',
            ],
            [
                'system_name' => 'citations.members.store',
                'display_name' => 'Associate a citation with a new individual',
            ],
            [
                'system_name' => 'citations.members.destroy',
                'display_name' => 'Dissociate an individual from a citation',
            ],
        ]);
    }
}
