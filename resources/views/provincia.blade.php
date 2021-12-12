@extends('main')


@push('head')
    <title>{{$data->nome}}</title>
    <meta name="description" content="{{$data->nome}}: elenco dei {{count($comuni)}} comuni e dei relativi stemmi">
    <link rel="canonical" href="{{url($data->slug)}}" />
@endpush
@section('content')
    <h1>{{$data->nome}}</h1>
    <hr>
    <nav
        style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);"
        aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item text-white"><a class="text-white" href="{{url("")}}">Home</a></li>
            @foreach( $breadcrumb as $nome => $url )
                <li class="breadcrumb-item text-white"><a class="text-white" href="{{url($url)}}">{{$nome}}</a></li>
            @endforeach
            <li class="breadcrumb-item active text-black" aria-current="page">{{$data->nome}}</li>
        </ol>
    </nav>
    <h2>
        Elenco dei comuni in {{$data->nome}}
    </h2>
    <div class="bg-white rounded">

{{--        <table class="table table-striped table-dark comuni-list">--}}

{{--            <tbody>--}}
{{--            @foreach($comuni as $comune)--}}
{{--                <tr>--}}
{{--                    <td>--}}
{{--                        @if( Storage::exists("public/stemmi/" . $comune->codice . ".jpg"))--}}
{{--                            <img class="stemma-small img-thumbnail"--}}
{{--                                 src="{{Storage::url("public/stemmi/" . $comune->codice . ".jpg")}}"/>--}}
{{--                        @endif--}}
{{--                    </td>--}}
{{--                    <td>--}}
{{--                        <a class=" text-white" href="{{url($comune->slug)}}">--}}
{{--                            {{$comune->nome}}--}}
{{--                        </a>--}}
{{--                    </td>--}}
{{--                </tr>--}}
{{--            @endforeach--}}
{{--            </tbody>--}}

{{--        </table>--}}

        <div class="d-flex flex-wrap bg-white comuni-list">
            @foreach($comuni as $comune)
            <a href="{{url($comune->slug)}}" class="m-1 flex-grow-1 d-flex align-items-center p-1 border border-2 rounded-2">
                @if( Storage::exists("public/stemmi/" . $comune->codice . ".jpg"))
                    <img class="stemma-small img-thumbnail"
                         alt="Stemma comune {{$comune->nome}}"
                         title="Stemma comune {{$comune->nome}}"
                         src="{{Storage::url("public/stemmi/" . $comune->codice . ".jpg")}}"/>
                @endif
                <span class="ms-2  " href="{{url($comune->slug)}}">
                    {{$comune->nome}}
                </span>
            </a>
            @endforeach
        </div>
    </div>
@endsection
