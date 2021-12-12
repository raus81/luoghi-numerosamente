@extends('main')

@push('head')
    <title>Comune di {{$data->nome}}</title>
    <meta name="description" content="Informazioni sul comune di {{$data->nome}}:  {{$infos['abitanti']}} abitanti,
superficie:{{$infos['superficie'] }}km2.@isset($infos['densita'])Densità:{{$infos['densita']}}ab./km2. @endisset @isset($infos['nome_abitanti'])Abitanti: {{$infos['nome_abitanti']}}. @endisset
    {{--        Parte della {{$infos['prov_tipo']}} di {{$infos['prov_nome']}}.--}}
    @isset( $infos['patrono'] )
        Patrono: {{$infos['patrono']}}.
                @endisset">

    <link rel="canonical" href="{{url($data->slug)}}"/>

@endpush
@section('content')

    <h1>Comune di {{$data->nome}}</h1>
    <hr>
    <nav
        style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);"
        aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item text-white"><a class="text-white" href="{{url('')}}">Home</a></li>
            @foreach( $breadcrumb as $nome => $url )
                <li class="breadcrumb-item text-white"><a class="text-white" href="{{url($url)}}">{{$nome}}</a></li>
            @endforeach
            <li class="breadcrumb-item active text-black" aria-current="page">Comune di {{$data->nome}}</li>
        </ol>
    </nav>
    <hr>
    <div class="row">
        <div class="col-12 col-lg-9 col-md-8 content">
            <h2>Descrizione</h2>
            <p class="bg-light p-1 rounded">
                Il comune di {{$data->nome}} conta di {{$infos['abitanti']}} abitanti, su di un territorio di
                {{$infos['superficie'] }} km<sup>2</sup>.

                @isset($infos['densita'])
                    La densità abitativa è di {{$infos['densita']}} abitanti/km<sup>2</sup>.
                @endisset

                @isset($infos['nome_abitanti'])
                    Gli abitanti di {{$data->nome}} sono chiamati <em> {{$infos['nome_abitanti']}} </em>. <br>
                @endisset

                Il comune fa parte della {{$infos['prov_tipo']}} di {{$infos['prov_nome']}}, insieme ad
                altri {{$numcomuni}} comuni.
                @isset( $infos['patrono'] )
                    Il patrono è {{$infos['patrono']}}.
                @endisset

            </p>
            <!-- Italia center -->
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="ca-pub-3475702324698098"
                 data-ad-slot="8428094320"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
            <h2>Stemma e informazioni</h2>
            <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start">
                @if( $stemma)
                    <div class="me-2 ">
                        <img alt="Stemma del comune di {{$data->nome}}" title="Stemma del comune di {{$data->nome}}"
                             class="img-thumbnail stemma" src="{{$stemma}}"/>
                    </div>
                @endif
                <div class="flex-grow-1 mt-2 mt-sm-0">
                    <div class=" border rounded p-2 bg-white">
                        <table class="table table-light table-striped w-100">
                            <caption>Informazioni sul comune di {{$data->nome}}</caption>

                            <tbody>
                            @isset( $infos['abitanti'] )
                                <tr>
                                    <td>Abitanti</td>
                                    <td>{{$infos['abitanti']}}</td>
                                </tr>
                            @endisset
                            @isset( $infos['superficie'] )
                                <tr>
                                    <td>Superficie</td>
                                    <td>{{$infos['superficie']}} <small>km<sup>2</sup></small></td>
                                </tr>
                            @endisset
                            @isset( $infos['densita'] )
                                <tr>
                                    <td>Densità</td>
                                    <td>{{$infos['densita']}} <small>abitanti/km<sup>2</sup> </small></td>
                                </tr>
                            @endisset
                            @isset( $infos['cap'] )
                                <tr>
                                    <td>CAP</td>
                                    <td>{{$infos['cap']}}</td>
                                </tr>
                            @endisset
                            @isset( $infos['codice_catastale'] )
                                <tr>
                                    <td>Codice catastale</td>
                                    <td>{{$infos['codice_catastale']}}</td>
                                </tr>
                            @endisset
                            @isset( $infos['prefisso'] )
                                <tr>
                                    <td>Prefisso telefonico</td>
                                    <td>{{$infos['prefisso']}}</td>
                                </tr>
                            @endisset

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <hr>
            <h2>Altre informazioni</h2>
            <div class="bg-light p-1 rounded">
                @isset($infos['frazioni_nuclei'] )
                    <h3>Frazioni e nuclei</h3>
                    <p class="bg-light p-1 rounded">
                        <strong>Frazioni e nuclei:</strong> Nel comune di {{$data->nome}} sono le
                        presenti {{count(explode(',',$infos['frazioni_nuclei']))}} tra frazioni e nuclei, di seguito
                        l'elenco:
                        {{$infos['frazioni_nuclei'] }}.
                    </p>
                @endisset
                @isset( $infos['zona_climatica'])
                    <h3>Zona climatica</h3>
                    <p>
                        La zona climatica del comune di {{$data->name}} è <strong>{{$infos['zona_climatica']}}</strong>
                        @isset($infos['gradi_giorno'])
                            , con <em>{{$infos['gradi_giorno']}}</em> gradi giorno
                        @endisset.
                        <br>
                        Le limitazioni sul riscaldamento sono:<br>
                        @switch($infos['zona_climatica'])
                            @case('A')
                            - 6 ore giornaliere di accensione, data di inizio 1º dicembre e 15 marzo data di fine.
                            @break
                            @case('B')
                            - 8 ore giornaliere di accensione, data di inizio 1º dicembre e 31 marzo data di fine.
                            @break
                            @case('C')
                            - 10 ore giornaliere di accensione, data di inizio 15 novembre e 31 marzo data di fine.
                            @break
                            @case('D')
                            - 12 ore giornaliere di accensione, data di inizio 1º novembre e 15 aprile data di fine.
                            @break
                            @case('E')
                            - 14 ore giornaliere di accensione, data di inizio 15 ottobre e 15 aprile data di fine.
                            @break
                            @case('F')
                            nessuna limitazione.
                            @break

                        @endswitch
                    </p>
                @endisset
                @isset($infos['altitudine'])
                    <h3> Altitudine </h3>
                    <p>
                        Il comune di {{$data->nome }} ha un'altitudine media sul livello del mare di
                        <strong>{{$infos['altitudine']}}</strong>m.
                        @isset($infos['altitudine_minima'])
                            <br/>L'altitudine minima è di <strong>{{$infos['altitudine_minima']}}</strong>m
                            <em>s.l.m.</em>.
                        @endisset
                        @isset($infos['altitudine_massima'])
                            <br/> L'altitudine massima è di <strong>{{$infos['altitudine_massima']}}</strong>m <em>s.l.m.</em>
                            .
                        @endisset
                    </p>
                @endisset
                @if($cognome != null)
                    <h3>Cognome più diffuso</h3>
                    <a href="{{url($data->slug.'/cognomi')}}">Il cognome più diffuso a {{$data->nome}}</a>
                    è <em>{{$cognome->cognome}}</em> con {{$cognome->quanti}} persone.
                @endif
            </div>

        </div>
        <div class="col-12 col-lg-3 col-md-4 mt-2 mt-lg-0">
            <h2>Altri comuni in provincia</h2>
            <!-- Italia side -->
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="ca-pub-3475702324698098"
                 data-ad-slot="9447686042"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
            <div class="rounded bg-light altri-comuni ">

                <div class="d-flex flex-wrap p-2 list">
                    @foreach($stessaprovincia as $nome => $url  )
                        <a class="flex-grow-1 text-center p-1" href="{{url($url)}}">{{$nome}}</a>
                    @endforeach

                </div>
            </div>
            @if(env('APP_DEBUG'))
                @php
                    dump( $infos);
                @endphp
            @endif
            {{--            Lato--}}
            {{--            @php--}}
            {{--                dump( $infos);--}}
            {{--            @endphp--}}
        </div>
    </div>
@endsection
