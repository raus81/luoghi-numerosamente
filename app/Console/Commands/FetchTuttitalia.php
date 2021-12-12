<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchTuttitalia extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:tuttitalia {action}';

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
     * @return array
     */
    private function getComuni(): array
    {
        $users = DB::select('select Denominazione,Codice_Comune_alfanumerico from comuni_raw cr  ');
        return $users;
    }

    private $baseUrl = "https://www.tuttitalia.it";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $action = $this->argument('action');
        if ($action == 'download') {
            $this->download();
        } else if ($action == 'parse') {
            $this->parse();
        }

        return Command::SUCCESS;
    }

    /**
     * @return array[]
     */
    private function download(): array
    {
        $exp = '@td class="vv"><div class="ow">.*?href="(.*?)"@';
        $data = file_get_contents("https://www.tuttitalia.it/province/");
        sleep(1.5);
        $matches = [];
        preg_match_all($exp, $data, $matches);

        foreach ($matches[1] as $match) {
            $url = $this->baseUrl . $match;
            $pFileCheck = md5($url);
            if (file_exists('data/tuttitalia/comuni/' . $pFileCheck)) {
                echo "Skipping $url\r\n";
                continue;
            }
            $pData = file_get_contents($url);
            sleep(1.5);
            $pMatches = [];
            preg_match_all('@<td><a href="(\\.\\..*?)">@', $pData, $pMatches);
            print_r($pMatches);
            foreach ($pMatches[1] as $cMatch) {
                $cUrl = $url . $cMatch;
                $filecheck = md5($cUrl);
                if (file_exists('data/tuttitalia/comuni/' . $filecheck)) {
                    echo "Skipping $cUrl\r\n";
                    continue;
                }
                $cData = file_get_contents($cUrl);
                sleep(3);
                $matchIstat = [];
                if (!preg_match('@<td class="lr">Codice Istat</td><td>(.*?)</td></tr>@', $cData, $matchIstat)) {
                    echo "Not found: " . $cUrl;
                    continue;
                }
                $filename = 'data/tuttitalia/comuni/' . $matchIstat[1];
                file_put_contents($filename, $cData);
                file_put_contents('data/tuttitalia/comuni/' . $filecheck, '');

                echo $cUrl . PHP_EOL;
            }
            sleep(0.5);
            file_put_contents('data/tuttitalia/comuni/' . $pFileCheck, '');
        }
        return array($matches, $pMatches, $matchIstat);
    }

    private function parse()
    {
        $comuni = $this->getComuni();
        foreach ($comuni as $comune) {
            $codiceComuneAlfanumerico = $comune->Codice_Comune_alfanumerico;
            $comune = $comune->Denominazione;
            $filename = 'data/tuttitalia/comuni/' . $codiceComuneAlfanumerico;
            if (!file_exists($filename)) {
                continue;
            }
            $data = file_get_contents($filename);

            $info = [];
            $info['abitanti'] = str_replace(".", "", $this->extractData('@<tr><td class="lr">Popolazione</td><td>(\s*[\d\\.]+\s*)<small>abitanti</small>@', $data));
            $info['superficie'] = $this->extractData('@<tr><td class="lr">Superficie</td><td>([\d,]+)@', $data);
            $info['densita'] = $this->extractData('@<tr><td class="lr">Densit.*?</td><td>([\d,]+)@', $data);
            $info['codice_catastale'] = $this->extractData('@>Codice catastale</td><td style="font-weight:bold;">(.*?)</td>@', $data);
            $info['prefisso'] = $this->extractData('@Prefisso</td><td><a href=".*?">(.*?)</a>@', $data);
            $info['cap'] = $this->extractData('@CAP</td><td colspan="2"><span class="xa">(\d+)@', $data);
            $info['sindaco'] = $this->extractData('@Sindaco</td><td>(?:<a.*?><img.*?/></a>)?<b><a.*?>(.*?)</a></b></td>@', $data);
            $info['indirizzo_municipio_html'] = $this->extractData('@<tr><td.*>Indirizzo Municipio</td><td>(.*?)</td>@', $data);
            $info['nome_abitanti'] = $this->extractData('@Nome abitanti</td><td>(.*?)</td></tr>@', $data);
            $info['patrono'] = $this->extractData('@Santo Patrono</td><td>(.*?)</td></tr>@', $data);
            $info['frazioni_nuclei'] = $this->extractData('@<td>Frazioni, Localit&agrave;<br />e Nuclei abitati</td><td><i>(.*?)</i> </td></tr>@', $data);
            $info['zona_sismica'] = $this->extractData('@><td><b>Zona sismica</b><div.*?>(.*?)</div>@', $data);
            $info['zona_climatica'] = $this->extractData('@<b>Zona climatica</b><div.*?>(.*?)</div>@', $data);
            $info['gradi_giorno'] = str_replace('.', '', $this->extractData('@<b>Gradi giorno</b><div.*?>([\d\.]+)</div>@', $data));
            $info['altitudine'] = $this->extractData('@<b>Altitudine</b>: (\d+) <small><abbr@', $data);
            $info['altitudine_minima'] = $this->extractData('@>m s.l.m.</abbr></small><br />minima: (\-?\d+)<br />massima@', $data);
            $info['altitudine_massima'] = $this->extractData('@>m s.l.m.</abbr></small><br />minima: \-?\d+<br />massima: (\-?\d+)</td><td>Misura@', $data);
            $info['lat_sess'] = html_entity_decode($this->extractData('@sistema sessagesimale</i><br />(.*?)<br />@', $data));
            $info['lon_sess'] = html_entity_decode($this->extractData('@sistema sessagesimale</i><br />.*?<br />(.*?)</p>@', $data));

            $info['lat_dec'] = html_entity_decode($this->extractData('@<p><i>sistema decimale</i><br />(.*?)<br />.*?</p>@', $data));
            $info['lon_dec'] = html_entity_decode($this->extractData('@<p><i>sistema decimale</i><br />.*?<br />(.*?)</p>@', $data));
            $info['regione'] = html_entity_decode($this->extractData('@<td class="lr">Regione</td><td><a href="/.*?/">(.*?)</a></td></tr>@', $data));


//            $test = [];
            $info['prov_tipo'] = $this->extractData('@class="lr">(.*)</td><td><a href="/.*?/provincia-.*?/">.*? \(<b>.*?</b>\)</a></td>@',$data);
            $info['prov_nome'] = $this->extractData('@class="lr">.*</td><td><a href="/.*?/provincia-.*?/">(.*?) \(<b>.*?</b>\)</a></td>@',$data);
            $info['prov_sigla'] = $this->extractData('@class="lr">.*</td><td><a href="/.*?/provincia-.*?/">.*? \(<b>(.*?)</b>\)</a></td>@',$data);

//            print_r($test );
//            continue;
            foreach ($info as $key => $value) {
                //continue;
                if ($value == null) {
                    continue;
                }
                DB::insert("INSERT OR IGNORE INTO info(codice, chiave, valore)VALUES(?, ?, ?)",
                    [$codiceComuneAlfanumerico, $key, $value]);
            }
            echo $comune . ' ' . $filename . PHP_EOL;
            print_r($info);

            //break;
        }
    }

    private function extractData($regex, $data)
    {
        $match = [];
        if (preg_match($regex, $data, $match)) {
            return $match[1];
        }
        return null;
    }
}
