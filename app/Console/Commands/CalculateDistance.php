<?php

namespace App\Console\Commands;

use App\Models\Place;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Location\Coordinate;
use Location\Distance\Vincenty;

class CalculateDistance extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:distance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $comuni = Place::query()->with(['infos' => function ($query) {
            $query->whereIn('chiave', ['lon_dec', 'lat_dec']);
        }])->whereLivello(4)->get();
        $comuni2 = Place::query()->whereLivello(4)->get();

        $data = $comuni->map(function ($comune) {
            $coordinate = $this->getCoord($comune->infos);
            return ['codice' => $comune->codice, 'coordinate' => $coordinate];
        });

        $len = count($data);

        $keyDone = [];

        for ($i = 0; $i < $len; $i++) {
            $comuneA = $data[$i];
            $coordA = $comuneA['coordinate'];
            $distances = [];

            echo "Calcolando {$comuneA['codice']}\r\n";
            for ($j = 0; $j < $len; $j++) {
                $comuneB = $data[$j];
                $coordB = $comuneB['coordinate'];
                $distance = $coordA->getDistance($coordB, new Vincenty());
                $distances[] = ['codice' => $comuneB['codice'], 'distance' => $distance];

                //echo $i . ' => ' . $j . ' : ' . $distance . PHP_EOL;
            }
            $this->saveDistances($comuneA['codice'], $distances);

        }
    }

    private function getCoord($infos): Coordinate
    {
        $coordinate = $infos->mapWithKeys(function ($item) {
            $value = str_replace(',', '.', preg_replace('@[^\d,]+@', '', $item->valore));
            return [$item->chiave => $value];
        });
        //dump($coordinate);
        return new Coordinate($coordinate['lat_dec'], $coordinate['lon_dec']);
    }

    private function saveDistances($codice, array $distances)
    {
        usort($distances, function ($a, $b) {
            return $a['distance'] - $b['distance'];
        });


        $toInsert = array_merge(array_slice($distances, 0, 16, true), array_slice($distances, -5, 5, true));

        foreach ($toInsert as $item) {
            if ($item['distance'] == 0) {
                continue;
            }
            DB::insert("INSERT OR IGNORE INTO distanza (codice_1, codice_2, metri) VALUES(?, ?, ?)",
                [$codice, $item['codice'], $item['distance']]);
        }
    }
}
