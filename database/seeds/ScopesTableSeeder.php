<?php

use Illuminate\Database\Seeder;
use App\ApiScope;

class ScopesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
        		'system_name' => 'import-scopus',
        		'display_name' => 'Import citation data from Scopus',
        	],
        	[
        		'system_name' => 'manipulate',
        		'display_name' => 'Create, modify, and delete citations',
        	],
        ]);
    }
}
