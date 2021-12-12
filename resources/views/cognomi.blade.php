@extends('main')

@push('head')
    <title>Cognomi più diffusi a {{$data->nome}}</title>
    <meta name="description" content="Scopri i cognomi più diffusi nel comune di {{$data->nome}}">

    <link rel="canonical" href="{{url($data->slug .'/cognomi')}}"/>

@endpush
@section('content')

    <h1>Cognomi più diffusi a {{$data->nome}}</h1>
    <hr>
        <nav
            style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);"
            aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item text-white"><a class="text-white" href="{{url('')}}">Home</a></li>
                @foreach( $breadcrumb as $nome => $url )
                    <li class="breadcrumb-item text-white"><a class="text-white" href="{{url($url)}}">{{$nome}}</a></li>
                @endforeach
                <li class="breadcrumb-item active text-black" aria-current="page">Cognomi nel comune di {{$data->nome}}</li>
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
            <div class="bg-white rounded rounded-2">

                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Cognome</th>
                        <th>Numero di persone</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cognomi as $cognome )
                        <tr>
                            <td>{{$cognome->cognome}}</td>
                            <td>{{$cognome->quanti}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="col-12 col-lg-3 col-md-4 mt-2 mt-lg-0">

        </div>
    </div>
@endsection
