@extends('main')

@push('head')
    <title>Statistiche popolazione del comune di {{$data->nome}}</title>
    <meta name="description" content="Scopri le statistiche sulla popolazione del Comune di {{$data->nome}}">

    <link rel="canonical" href="{{url($data->slug .'/popolazione')}}"/>

@endpush
@section('content')

    <h1>Popolazione Comune di {{$data->nome}}</h1>
    <hr>
    <nav
        style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);"
        aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item text-white"><a class="text-white" href="{{url('')}}">Home</a></li>
            @foreach( $breadcrumb as $nome => $url )
                <li class="breadcrumb-item text-white"><a class="text-white" href="{{url($url)}}">{{$nome}}</a></li>
            @endforeach
            <li class="breadcrumb-item active text-black" aria-current="page">Popolazione nel comune
                di {{$data->nome}}</li>
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
            <div>
                @php
                    $f = new NumberFormatter("it", NumberFormatter::SPELLOUT);
                @endphp
                <h2>Popolazione</h2>
                <p class="p-1 bg-white rounded rounded-2">
                    La popolazione del Comune di {{$data->nome}} è composta da
                    <strong>{{$f->format($pop->TOTAL->T->T)}}</strong> persone di cui
                    <strong>{{$f->format($pop->TOTAL->F->T)}}</strong> femmine e
                    <strong>{{$f->format($pop->TOTAL->M->T)}}</strong> maschi.<Br>

                </p>
                @isset( $pop->TOTAL->T->C )
                    <h2>Celibi/nubili</h2>
                    <div class="bg-white rounded rounded-2">
                        <p class=" p-1">
                            Le persone celibi/nubili sono <strong>{{$f->format($pop->TOTAL->T->C)}}</strong> composte
                            da:
                        </p>
                        <ul>
                            @isset($pop->TOTAL->M->C  )
                                <li>
                                    <strong>{{$f->format($pop->TOTAL->M->C)}}</strong> uomini celibi
                                </li>
                            @endisset
                            @isset($pop->TOTAL->F->C  )
                                <li>
                                    <strong>{{$f->format($pop->TOTAL->F->C)}}</strong> donne nuibli
                                </li>
                            @endisset
                        </ul>
                    </div>

                @endisset
                @isset( $pop->TOTAL->T->S )
                    <h2>Coniugati</h2>
                    <div class="bg-white rounded rounded-2"><p class="p-1">
                            Le persone coniugate sono <strong>{{$f->format($pop->TOTAL->T->S)}}</strong> composte
                            da:
                        </p>
                        <ul>
                            @isset($pop->TOTAL->M->S  )
                                <li>
                                    <strong>{{$f->format($pop->TOTAL->M->S)}}</strong> uomini coniugati
                                </li>
                            @endisset
                            @isset($pop->TOTAL->F->S  )
                                <li>
                                    <strong>{{$f->format($pop->TOTAL->F->S)}}</strong> donne coniugate
                                </li>
                            @endisset
                        </ul>
                    </div>
                @endisset
                @isset( $pop->TOTAL->T->V )
                    <h2>Vedovi </h2>
                    <div class="bg-white rounded rounded-2"><p class="p-1">
                            Le persone vedove sono <strong>{{$f->format($pop->TOTAL->T->S)}}</strong>, composte
                            da:
                        </p>
                        <ul>
                            @isset($pop->TOTAL->M->V  )
                                <li>
                                    <strong>{{$f->format($pop->TOTAL->M->V)}}</strong> uomini vedovi
                                </li>
                            @endisset
                            @isset($pop->TOTAL->F->V  )
                                <li>
                                    <strong>{{$f->format($pop->TOTAL->F->V)}}</strong> donne vodove
                                </li>
                            @endisset
                        </ul>
                    </div>
                @endisset
                @isset( $pop->TOTAL->T->D )
                    <h2>Divorziati </h2>
                    <div class="bg-white rounded rounded-2"><p class="p-1">
                            Le persone divorziate sono <strong>{{$f->format($pop->TOTAL->T->D)}}</strong>, composte
                            da:
                        </p>
                        <ul>
                            @isset($pop->TOTAL->M->D  )
                                <li>
                                    <strong>{{$f->format($pop->TOTAL->M->D)}}</strong> uomini divorziati
                                </li>
                            @endisset
                            @isset($pop->TOTAL->F->D  )
                                <li>
                                    <strong>{{$f->format($pop->TOTAL->F->D)}}</strong> donne divorziate
                                </li>
                            @endisset
                        </ul>
                    </div>
                @endisset
            </div>
            <h2>Dettaglio statistiche</h2>
            <div class="bg-white rounded rounded-2 table-responsive ">
                <table class="table table-striped popolazione">
                    <thead>
                    <tr>
                        <th><strong>Età</strong></th>
                        <th>Nubili/Celibi</th>
                        <th>Coniugate/i</th>
                        <th>Vedove/i</th>
                        <th>Divorziate/i</th>
                        <th>Maschi</th>
                        <th>Femmine</th>
                    </tr>

                    </thead>
                    <tbody>
                    @foreach( $pop as $key  => $value )
                        @if( $key == 'TOTAL')
                            @continue
                        @endif
                        <tr>

                            <td>{{$key}}</td>
                            <td>
                                {{$pop->{$key}->T->C ?? '0'}}
                                @if( $pop->{$key}->T->C ?? 0 )
                                    <br>
                                    <small>
                                        <em>Maschi:</em> {{$pop->{$key}->M->C ?? ''}}
                                        <br><em>Femmine:</em> {{$pop->{$key}->F->C ?? ''}}
                                    </small>
                                @endif
                            </td>
                            <td>
                                {{$pop->{$key}->T->S ?? '0'}}
                                @if( $pop->{$key}->T->S ?? 0 )

                                    <br>
                                    <small>
                                        <em>Maschi:</em> {{$pop->{$key}->M->S ?? ''}}
                                        <br><em>Femmine:</em> {{$pop->{$key}->F->S ?? ''}}
                                    </small>
                                @endif
                            </td>
                            <td>{{$pop->{$key}->T->V ?? '0'}}
                                @if( $pop->{$key}->T->V ?? 0 )

                                    <br>
                                    <small>
                                        <em>Maschi:</em> {{$pop->{$key}->M->V ?? ''}}
                                        <br><em>Femmine:</em> {{$pop->{$key}->F->V ?? ''}}
                                    </small>
                                @endif
                            </td>
                            <td>{{$pop->{$key}->T->D ?? '0'}}
                                @if( $pop->{$key}->T->D ?? 0 )

                                    <br>
                                    <small>
                                        <em>Maschi:</em> {{$pop->{$key}->M->D ?? ''}}
                                        <br><em>Femmine:</em> {{$pop->{$key}->F->D ?? ''}}
                                    </small>
                                @endif</td>
                            <td>{{$pop->{$key}->M->T ?? '0'}}

                            <td>{{$pop->{$key}->F->T ?? '0'}}

                        </tr>
                    @endforeach
                    <tfoot>
                    @php
                $key = 'TOTAL';
@endphp
                    <tr>

                        <td>Totale</td>
                        <td>
                            {{$pop->{$key}->T->C ?? '0'}}
                            @if( $pop->{$key}->T->C ?? 0 )
                                <br>
                                <small>
                                    <em>Maschi:</em> {{$pop->{$key}->M->C ?? ''}}
                                    <br><em>Femmine:</em> {{$pop->{$key}->F->C ?? ''}}
                                </small>
                            @endif
                        </td>
                        <td>
                            {{$pop->{$key}->T->S ?? '0'}}
                            @if( $pop->{$key}->T->S ?? 0 )

                                <br>
                                <small>
                                    <em>Maschi:</em> {{$pop->{$key}->M->S ?? ''}}
                                    <br><em>Femmine:</em> {{$pop->{$key}->F->S ?? ''}}
                                </small>
                            @endif
                        </td>
                        <td>{{$pop->{$key}->T->V ?? '0'}}
                            @if( $pop->{$key}->T->V ?? 0 )

                                <br>
                                <small>
                                    <em>Maschi:</em> {{$pop->{$key}->M->V ?? ''}}
                                    <br><em>Femmine:</em> {{$pop->{$key}->F->V ?? ''}}
                                </small>
                            @endif
                        </td>
                        <td>{{$pop->{$key}->T->D ?? '0'}}
                            @if( $pop->{$key}->T->D ?? 0 )

                                <br>
                                <small>
                                    <em>Maschi:</em> {{$pop->{$key}->M->D ?? ''}}
                                    <br><em>Femmine:</em> {{$pop->{$key}->F->D ?? ''}}
                                </small>
                            @endif</td>
                        <td>{{$pop->{$key}->M->T ?? ''}}

                        <td>{{$pop->{$key}->F->T ?? ''}}

                    </tr>
                    </tfoot>
                    </tbody>

                </table>
            </div>


        </div>
        <div class="col-12 col-lg-3 col-md-4 mt-2 mt-lg-0">

        </div>
    </div>
@endsection
