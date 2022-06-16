<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\ApiScope;

class ScopesTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $table = (new ApiScope())->getTable();

        DB::table($table)->delete();
        DB::table($table)->insert([
        	[
        		'system_name' => 'all',
        		'display_name' => 'Combination of all available scopes',
        	],
        	[
        		'system_name' => 'citations.import.scopus',
        		'display_name' => 'Import citation data from Scopus',
        	],
        	[
        		'system_name' => 'citations.manipulate',
        		'display_name' => 'Create, modify, and delete citations',
        	],
        ]);
    }
}
