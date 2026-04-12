<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
   
        [
            'type' => 'general',
            'field_type' => 'text',
            'dataset' => null,
            'data_source' => '',
            'code' => 'contact_email',
            'title' => 'contact Email',
            'placeholder' => '',
            'value' => '',
            'status' => 1,
        ],
        [
            'type' => 'strava',
            'field_type' => 'text',
            'dataset' => null,
            'data_source' => '',
            'code' => 'client_id',
            'title' => 'Client Id',
            'placeholder' => '',
            'value' => '193132',
            'status' => 1,
        ],
        [
            'type' => 'strava',
            'field_type' => 'password',
            'dataset' => null,
            'data_source' => '',
            'code' => 'client_secret',
            'title' => 'Client Secret',
            'placeholder' => '',
            'value' => '5af50729cdec38b692bb93f8bb931a5622a26e21',
            'status' => 1,
        ]
      
        
    ];
    foreach ($settings as $setting) {
         Setting::updateOrCreate([
                'code' => $setting['code'],
            ], $setting);
    }
    }

}
