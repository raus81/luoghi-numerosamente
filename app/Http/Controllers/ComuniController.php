<?php

namespace App\Http\Controllers;

use App\Models\Info;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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


    function comune($regione, $comune)
    {
        //$comuneObj = DB::selectOne("SELECT * FROM places WHERE livello = 4 AND nome like ?", [$comune]);
        $comuneObj = Place::query()->where([['livello', '=', 4], ['nome', 'like', str_replace('-', '_', $comune)]])->first();
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

        //dd( $infos );
        return view('comune', ['data' => $comuneObj,
            'infos' => $infos,
            'stemma' => $stemmaFile,
            'numcomuni' => $numComuniProvincia,
            'stessaprovincia' => $comuniStessaProvincia,
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
            ['livello','!=',2]
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
}
