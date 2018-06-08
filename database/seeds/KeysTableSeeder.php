<?php

use Illuminate\Database\Seeder;
use App\ApiKey;

class KeysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $table = (new ApiKey())->getTable();
        DB::table($table)->delete();

        // key with all scopes
        $allKey = ApiKey::create([
            'key' => sha1(bin2hex(random_bytes(5))),
        ]);
        $allKey->scopes()->attach('all');

        // key with "import-scopus" scope
        $importKey = ApiKey::create([
            'key' => sha1(bin2hex(random_bytes(5))),
        ]);
        $importKey->scopes()->attach('import-scopus');

        // key with "manipulate" scope
        $manipulateKey = ApiKey::create([
            'key' => sha1(bin2hex(random_bytes(5))),
        ]);
        $manipulateKey->scopes()->attach('manipulate');
    }
}
