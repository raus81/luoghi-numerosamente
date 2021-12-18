@extends('main')

@push('head')
    <title>Statistiche popolazione del comune di {{$data->nome}}</title>
    <meta name="description" content="Scopri i comuni più vicini e i comuni più distanti al Comune di {{$data->nome}}">

    <link rel="canonical" href="{{url($data->slug .'/distanze')}}"/>

@endpush
@section('content')

    <h1>Comune di {{$data->nome}}: vicini e lontani</h1>
    <hr>
    <nav
        style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);"
        aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item text-white"><a class="text-white" href="{{url('')}}">Home</a></li>
            @foreach( $breadcrumb as $nome => $url )
                <li class="breadcrumb-item text-white"><a class="text-white" href="{{url($url)}}">{{$nome}}</a></li>
            @endforeach
            <li class="breadcrumb-item active text-black" aria-current="page">Vicini e lontani</li>
        </ol>
    </nav>
    <hr>
    <div class="row">
        <div class="col-12 col-lg-9 col-md-8 content">
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
            <div class="  ">
                @php
                    $f = new NumberFormatter("it", NumberFormatter::SPELLOUT);
                @endphp
                <h2>Comuni più vicino e più lontano</h2>
                <p class="p-1 rounded rounded-2 bg-white">
                    Il comune più vicino al Comune di {{$data->nome}} è {{$distanze->first()->place2->nome}} e dista
                    <strong>{{$f->format(floor( $distanze->first()->metri /1000))}}</strong> <em>km</em>.
                    <br>
                    Mentri il comune più lontano dal comune di {{$data->nome}} è il Comune
                    di {{$distanze->last()->place2->nome}}<em>({{$distanze->last()->place2->upLevel->nome}})</em> e
                    dista
                    <strong>{{$f->format(floor( $distanze->last()->metri /1000))}}</strong> <em>km</em>.
                </p>
                <h2>Dettaglio comuni vicini e lontani</h2>
                <div class="rounded rounded-2 bg-white">
                    <table class="table distanze">
                        <thead>
                        <tr>
                            <th>Comune</th>
                            <th>Distanza</th>
                        </tr>
                        </thead>
                        <tbody class="vicini">
                        @php
                        $vicini = $distanze->slice(0,15);
                        @endphp
                        @foreach($vicini as $distanza)
                            <tr>
                                <td>{{$distanza->place2->nome}}<br>
                                <small>{{$distanza->place2->upLevel->nome}}</small></td>
                                <td>{{number_format( $distanza->metri/1000,3,',','.')}} <em>km</em></td>
                            </tr>
                        @endforeach

                        </tbody>
                        <tbody class="distanti">
                        @php
                            $lontani = $distanze->slice(15,20);
                        @endphp
                        @foreach($lontani as $distanza)
                            <tr>
                                <td>{{$distanza->place2->nome}}<br>
                                    <small>{{$distanza->place2->upLevel->nome}}</small></td>
                                <td>{{number_format( $distanza->metri/1000,3,',','.')}} <em>km</em></td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>


        </div>
        <div class="col-12 col-lg-3 col-md-4 mt-2 mt-lg-0">

        </div>
    </div>
@endsection
