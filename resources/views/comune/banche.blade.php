@if(isset($infos['banche']))

    @php
        $bancheJson  = json_decode($infos['banche'],true) ;

    @endphp
    @if(env('APP_DEBUG'))
        @php
            dump( $infos['banche']);
        @endphp
    @endif
    <div>

    </div>
    <div class="bg-light p-1 rounded mt-4">
        <h3>Banche</h3>
        <p>

            @foreach($bancheJson as $banca )
                @if(    isset( $banca['nome'] )&& isset($banca['indirizzo'] ) )
                    <strong>{{$banca['nome']}}</strong><br>
                    <i class="fa-solid fa-location-dot"></i> {{$banca['indirizzo']}}<br>
                    @if(  isset($banca['telefono']) )
                        <i class="fa-solid fa-phone"></i> {{str_replace(';','',$banca['telefono'])}}
                        @if(  isset($banca['fax']) )
                             - <i class="fa-solid fa-fax"></i> {{str_replace(';','',$banca['fax'])}}

                        @endif
                        <br>
                    @endif
                    @if( !$loop->last)
                        <br>
                    @endif
                @endif
            @endforeach
        </p>

    </div>
@endif
