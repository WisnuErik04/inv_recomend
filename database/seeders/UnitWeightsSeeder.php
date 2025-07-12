<?php

namespace Database\Seeders;

use App\Models\UnitWeight;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitWeightsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = [
            ["nama" => 'kg', "keterangan" => 'kilogram'],
            ["nama" => 'hg', "keterangan" => 'hektogram (ons)'],
            ["nama" => 'dag', "keterangan" => 'dekagram'],
            ["nama" => 'g', "keterangan" => 'gram'],
            ["nama" => 'dg', "keterangan" => 'desigram'],
            ["nama" => 'cm', "keterangan" => 'centigram'],
            ["nama" => 'mm', "keterangan" => 'miligram'],
        ];
        
        foreach ($datas as $data) {
            UnitWeight::firstOrCreate($data);
        }
    }
}
