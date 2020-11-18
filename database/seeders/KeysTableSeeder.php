<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\ApiKey;

class KeysTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        // THIS SEEDER SHOULD ONLY BE RUN DURING DEVELOPMENT AND TO GENERATE
        // AN INITIAL SET OF API KEYS; THIS SEEDER DESTROYS DATA BEFORE IT
        // RE-CREATES IT

        $table = (new ApiKey())->getTable();
        $scopePivotTable = 'key_scope';
        DB::table($table)->delete();
        DB::table($scopePivotTable)->delete();

        // key with all scopes
        $allKey = ApiKey::create([
            'key' => sha1(bin2hex(random_bytes(5))),
        ]);
        $allKey->scopes()->attach('all');

        // key with "import-scopus" scope
        $importKey = ApiKey::create([
            'key' => sha1(bin2hex(random_bytes(5))),
        ]);
        $importKey->scopes()->attach('citations.import.scopus');

        // key with "manipulate" scope
        $manipulateKey = ApiKey::create([
            'key' => sha1(bin2hex(random_bytes(5))),
        ]);
        $manipulateKey->scopes()->attach('citations.manipulate');
    }
}
