<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TestData;
use Exception;

class TestMongoConnection extends Command
{
    protected $signature = 'mongo:test';
    protected $description = 'tests basics MongoDB operations';

    public function handle()
    {
        print(__METHOD__ . "\n");
        try {

            $this->info("testing mongodb connection...");

            $test = TestData::create([
                'title' => 'connection test',
                'description' => 'MongoDB connection OK',
                'created_at' => now(),
            ]);

            $this->info("Document $test->_id has been succesfully created");

            $found = TestData::find($test->_id);
            $this->info("document has been found");
            $this->line(json_encode($found, JSON_PRETTY_PRINT));

            $count = testData::count(); 
            $this->info("$count Documents found in this collection");

            $this->info("Connection OK"); 
        } catch (Exception $e) {
            throw new Exception( "Fail during test" . $e->getMessage());
        }
    }
}
