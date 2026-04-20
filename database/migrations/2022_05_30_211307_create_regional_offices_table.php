<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('regional_offices', function (Blueprint $table) {
            $table->id();
            $table->string('region');
            $table->string('location');
            $table->string('address');
            $table->string('telephone');
            $table->string('fax')->nullable();
            $table->timestamps();
        });

        DB::table('regional_offices')->insert([
            [
                'region' => 'Central Region',
                'location' => 'Serowe',
                'address' => 'Private Bag 091 Serowe',
                'telephone' => '4631820 / 4632326',
                'fax' => '4632324 / 4631647'
            ],
            [
                'region' => 'Chobe Region',
                'location' => 'Kasane',
                'address' => 'P.O. Box 162 Kasane',
                'telephone' => '6250517 / 6252163',
                'fax' => '6250191'
            ],
            [
                'region' => 'Ghanzi Region',
                'location' => 'Ghanzi',
                'address' => 'Private Bag 22 Ghanzi',
                'telephone' => '6596883 / 6596322',
                'fax' => '6596915'
            ],
            [
                'region' => 'Kgalagadi Region',
                'location' => 'Tsabong',
                'address' => 'P.O. Box 288 Tsabong',
                'telephone' => '6540210 / 6540018',
                'fax' => '6540618 / 6540000'
            ],
            [
                'region' => 'Kgatleng Region',
                'location' => 'Mochudi',
                'address' => 'P.O. Box 199 Mochudi',
                'telephone' => '5777226 / 5777723',
                'fax' => '5777879'
            ],
            [
                'region' => 'Kweneng Region',
                'location' => 'Molepolole',
                'address' => 'Private Bag 045 Molepolole',
                'telephone' => '5921724 / 5921609',
                'fax' => '5905157'
            ],
            [
                'region' => 'North East Region',
                'location' => 'Francistown',
                'address' => 'Private Bag F251 Francistown',
                'telephone' => '2410157 / 2416448',
                'fax' => '2415606 / 2410838'
            ],
            [
                'region' => 'North West Region',
                'location' => 'Maun',
                'address' => 'Private Bag 324 Maun',
                'telephone' => '6860348',
                'fax' => '6860629 / 6860646'
            ],
            [
                'region' => 'South East Region',
                'location' => 'Gaborone',
                'address' => 'P.O. Box 00343 Gaborone',
                'telephone' => '3901263 / 3975888',
                'fax' => '3975899'
            ],
            [
                'region' => 'Southern Region',
                'location' => 'Kanye',
                'address' => 'Private Bag 003 Kanye',
                'telephone' => '5441882 / 5441876',
                'fax' => '5441880 / 5442042'
            ],
        ]);
    }

    public function down() {
        Schema::dropIfExists('regional_offices');
    }
};
