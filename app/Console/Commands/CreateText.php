<?php

namespace App\Console\Commands;

use App\Models\Info;
use App\Models\Place;
use Illuminate\Console\Command;

class CreateText extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:text';

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
        $query = Place::query()->whereLivello(4);
        $comuni = $query->limit(1000)->get();
        foreach ($comuni as $comune) {


            $provincia = $comune->upLevel;
            $regione = $provincia->upLevel;
            $comuneFull = $comune->nome . ', ' . $provincia->nome . ', ' . $regione->nome;
            $infos = $comune->infos->mapWithKeys(function ($info) {
                return [$info->chiave => $info->valore];
            });
            if (isset($infos['text'])) {
                continue;
            }

            $infosWithComuni = Info::query()->where([['chiave', '=', 'prov_nome'], ['valore', 'like', $infos['prov_nome']]])->with('comune')->get();
            $numComuniProvincia = count($infosWithComuni) - 1;


            $data = $this->infosToText($infos);
            $text = $comuneFull . '.' . PHP_EOL . implode($data, '.' . PHP_EOL) . '.' . PHP_EOL;

            $text .= 'Il comune fa parte della ' . $infos['prov_tipo'] . ' di ' . $infos['prov_nome'] . ', insieme ad altri ' . $numComuniProvincia . ' comuni.' . PHP_EOL;

            echo 'Scrivi un testo sul comune di ' . $text . PHP_EOL . PHP_EOL;

            //return Command::SUCCESS;
        }
        return Command::SUCCESS;
    }

    private function infosToText($infos)
    {
        $data = [];
        if (isset($infos['abitanti'])) {
            $data[] = 'Abitanti ' . $infos['abitanti'];
        }
        if (isset($infos['altitudine'])) {
            $data[] = 'altitudine ' . $infos['altitudine'] . 'm sul livello del mare';
        }
        if (isset($infos['altitudine_massima']) && isset($infos['altitudine_minima'])) {
            $data[] = 'altezza minima ' . $infos['altitudine_minima'] . 'm s.l.m. e altitudine massima ' . $infos['altitudine_massima'] . 'm s.l.m.';
        }
        if (isset($infos['cap'])) {
            $data[] = 'CAP ' . $infos['cap'];
        }
        if (isset($infos['codice_catastale'])) {
            $data[] = 'codice catastale' . $infos['codice_catastale'];
        }
        if (isset($infos['densita'])) {
            $data[] = 'densita di popolazione ' . $infos['densita'] . ' abitanti/km2';
        }
        if (isset($infos['frazioni_nuclei'])) {
            $data[] = 'frazioni e nuclei abitativi: ' . $infos['frazioni_nuclei'];
        }
        if (isset($infos['gradi_giorno'])) {
            $data[] = 'gradi giorni' . $infos['gradi_giorno'];
        }
        if (isset($infos['sindaco'])) {
            $data[] = 'sindaco ' . $infos['sindaco'];
        }
        if (isset($infos['superficie'])) {
            $data[] = 'superficie ' . $infos['superficie'] . 'km2';
        }
        if (isset($infos['zona_climatica'])) {
            $data[] = 'zona climatica ' . $infos['zona_climatica'];
        }
        if (isset($infos['zona_sismica'])) {
            $data[] = 'zona_sismica ' . $infos['zona_sismica'];
        }

        if (isset($infos['patrono'])) {
            $data[] = 'Il patrono ?? ' . $infos['patrono'];
        }
        return $data;
    }
}
