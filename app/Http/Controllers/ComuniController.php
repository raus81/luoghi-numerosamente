<?php

namespace App\Http\Controllers;

use App\Models\Cognome;
use App\Models\Info;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Watson\Sitemap\Facades\Sitemap;

class ComuniController extends Controller {

    function provincia(Request $request)
    {

        $slug = substr($request->getRequestUri(), 1);
        $provinciaObj = Place::query()->where([
            ['livello', '=', 3],
            ['slug', 'like', $slug]
        ])->firstOrFail();


        $comuni = $provinciaObj->downLevel;
        $breadcrumbs = [];
        $regioneObj = $provinciaObj->upLevel;
        // $breadcrumbs[$regioneObj->nome] = $regioneObj->slug;


        return view('provincia', [
            'breadcrumb' => $breadcrumbs,
            'comuni' => $comuni,
            'data' => $provinciaObj]);

    }

    function popolazione($regione, $comune)
    {
        //$comuneObj = DB::selectOne("SELECT * FROM places WHERE livello = 4 AND nome like ?", [$comune]);
        $comuneObj = Place::query()->where([['livello', '=', 4], ['nome', 'like', str_replace('-', '_', $comune)]])->first();

        $breadcrumbs = $this->getBreadcrumb($comuneObj);
        $fileJson = 'info/istat/' . $comuneObj->codice . '.json';

        if (!Storage::exists($fileJson)) {
            return redirect('/');
        }
        $data = Storage::get($fileJson);
        $json = json_decode($data);

        return view('popolazione', [
            'data' => $comuneObj,
            'pop' => $json,
            'breadcrumb' => $breadcrumbs
        ]);
    }


    function distanze($regione, $comune)
    {
        //$comuneObj = DB::selectOne("SELECT * FROM places WHERE livello = 4 AND nome like ?", [$comune]);
        $comuneObj = Place::query()->where([['livello', '=', 4], ['nome', 'like', str_replace('-', '_', $comune)]])->first();

        $distanze = $comuneObj->distanze()->with('place2.upLevel')->get()
            ->sortBy('metri', SORT_NUMERIC);


        return view('distanze', [
            'data' => $comuneObj,
            'distanze' => $distanze,
            'breadcrumb' => $this->getBreadcrumb($comuneObj)
        ]);

    }

    function cognomi($regione, $comune)
    {
        //$comuneObj = DB::selectOne("SELECT * FROM places WHERE livello = 4 AND nome like ?", [$comune]);
        $comuneObj = Place::query()->where([['livello', '=', 4], ['nome', 'like', str_replace('-', '_', $comune)]])->first();

        $breadcrumbs = $this->getBreadcrumb($comuneObj);


        $cognomi = $comuneObj->cognomi()->orderBy('quanti', 'desc')->get();
        //dd($cognomi);
        return view('cognomi', [
            'data' => $comuneObj,
            'cognomi' => $cognomi,
            'breadcrumb' => $breadcrumbs
        ]);
    }

    function comune($regione, $comune)
    {
        //$comuneObj = DB::selectOne("SELECT * FROM places WHERE livello = 4 AND nome like ?", [$comune]);
        $comuneObj = Place::query()->where([['livello', '=', 4], ['nome', 'like', str_replace('-', '_', $comune)]])->firstOrFail();


        if ($comuneObj->parent_parent != $regione) {
            return redirect($comuneObj->slug);
        }
        $infos = $comuneObj->infos->mapWithKeys(function ($item, $key) {
            return [$item->chiave => $item->valore];
        });
        $stemmaFile = null;
        if (Storage::exists("public/stemmi/" . $comuneObj->codice . ".jpg")) {
            $stemmaFile = Storage::url("public/stemmi/" . $comuneObj->codice . ".jpg");
        }

        $infosWithComuni = Info::query()->where([['chiave', '=', 'prov_nome'], ['valore', 'like', $infos['prov_nome']]])->with('comune')->get();

        $numComuniProvincia = count($infosWithComuni) - 1;

        $comuniStessaProvincia = $infosWithComuni
            ->map(function ($info) {
                return $info->comune;
            })
            ->filter(function ($comune) use ($comuneObj) {
                return $comune->nome != $comuneObj->nome;
            })
            ->mapWithKeys(function ($comune, $index) {
                //dump( $info);
                return [$comune->nome => '/' . $comune->slug];
            });

        $breadcrumbs = [];
        $provincia = $comuneObj->upLevel;
        $regioneObj = $provincia->upLevel;

        // $breadcrumbs[$regioneObj->nome] = $regioneObj->slug;
        $breadcrumbs[$provincia->nome] = $provincia->slug;

        $cognome = $comuneObj->cognomi()->orderBy('quanti', 'desc')->first();

        $fileJson = 'info/istat/' . $comuneObj->codice . '.json';

        $hasStatistiche = false;
        if (Storage::exists($fileJson)) {
            $hasStatistiche = true;
        }

        $distanza = $comuneObj->distanze()->with('place2.upLevel')->orderBy('metri')->first();

        return view('comune', ['data' => $comuneObj,
            'cognome' => $cognome,
            'infos' => $infos,
            'stemma' => $stemmaFile,
            'numcomuni' => $numComuniProvincia,
            'stessaprovincia' => $comuniStessaProvincia,
            'statistiche' => $hasStatistiche,
            'vicino' => $distanza,
            'breadcrumb' => $breadcrumbs]);
    }

    public function suggester(Request $request)
    {
        $params = $request->all();
        if (!isset($params['search'])) {
            return [];
        }
        $search = $params['search'];
        return Place::query()->where([
            ['search', 'like', $search . '%'],
            ['livello', '!=', 2]
        ])
            ->orderBy(DB::raw('LENGTH("search") '))
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return ['nome' => $item->nome, 'url' => url($item->slug)];
            });
    }


    public function home()
    {
        $places = Place::query()->where('livello', 4)->inRandomOrder()->limit(20)->get()
            ->mapWithKeys(function ($place) {
                return [$place->nome => url($place->slug)];
            });

        return view('home', ['places' => $places]);
    }


    public function sitemap()
    {
        $places = Place::query()->where([['livello', '!=', 2]])->get();

        $today = Carbon::today();
        $today->setHour(0)->setMinute(0)->setSecond(0);
        foreach ($places as $place) {
            $tag = Sitemap::addTag(url($place->slug), $today, 'daily', '0.8');
            if ($place->livello == 4 && Storage::exists("public/stemmi/" . $place->codice . ".jpg")) {
                $stemmaFile = url(Storage::url("public/stemmi/" . $place->codice . ".jpg"));
                $tag->addImage($stemmaFile, "Stemma del comune di {$place->nome}");
            }
        }
        return Sitemap::render();
    }

    public function sitemapExtra()
    {
        $places = Place::query()->where([['livello', '=', 4]])->get();

        $today = Carbon::today();
        $today->setHour(0)->setMinute(0)->setSecond(0);
        foreach ($places as $place) {
            if ($place->cognomi->count()) {
                Sitemap::addTag(url($place->slug . '/cognomi'), $today, 'daily', '0.8');
            }
            Sitemap::addTag(url($place->slug . '/distanze'), $today, 'daily', '0.8');
            $fileJson = 'info/istat/' . $place->codice . '.json';

            if (Storage::exists($fileJson)) {
                Sitemap::addTag(url($place->slug . '/popolazione'), $today, 'daily', '0.8');
            }

        }
        return Sitemap::render();

    }

    /**
     * @param object|null $comuneObj
     * @return array
     */
    private function getBreadcrumb(?object $comuneObj): array
    {
        $breadcrumbs = [];
        $provincia = $comuneObj->upLevel;
        $regioneObj = $provincia->upLevel;

        // $breadcrumbs[$regioneObj->nome] = $regioneObj->slug;
        $breadcrumbs[$provincia->nome] = $provincia->slug;
        $breadcrumbs[$comuneObj->nome] = $comuneObj->slug;
        return $breadcrumbs;
    }
}
