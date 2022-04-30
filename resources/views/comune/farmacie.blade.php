@if(isset($infos['farmacie']))

    @php
        $farmacieJson  = json_decode($infos['farmacie'],true) ;

    @endphp
    @if(env('APP_DEBUG'))
        @php
            dump( $infos['farmacie']);
        @endphp
    @endif
    <div>

    </div>
    <div class="bg-light p-1 rounded mt-4">
        <h3>Farmacie</h3>
        <p>

            @foreach($farmacieJson['farmacie'] as $farmacia )
                @if(    isset( $farmacia['nome'] )&& isset($farmacia['indirizzo'] )&& isset($farmacia['telefono'] )&& isset($farmacia['codice'] )&& isset($farmacia['piva'] ) )
                    <strong>{{$farmacia['nome']}}</strong><br>
                    <i class="fa-solid fa-location-dot"></i> {{$farmacia['indirizzo']}}<br>
                    <i class="fa-solid fa-phone"></i> {{str_replace(';','',$farmacia['telefono'])}} -
                    codice {{$farmacia['codice']}} - p.iva {{$farmacia['piva']}}<br>
                    @if( !$loop->last)
                        <br>
                    @endif
                @endif
            @endforeach
        </p>
        @if(isset($farmacieJson['parafarmacie']))
            <h3>Para-farmacie</h3>
            <p>

                @foreach($farmacieJson['parafarmacie'] as $farmacia )
                    @if(    isset( $farmacia['nome'] )&& isset($farmacia['indirizzo'] )&& isset($farmacia['telefono'] )&& isset($farmacia['codice'] )&& isset($farmacia['piva'] ) )
                        <strong>{{$farmacia['nome']}}</strong><br>
                        <i class="fa-solid fa-location-dot"></i> {{$farmacia['indirizzo']}}<br>
                        <i class="fa-solid fa-phone"></i> {{str_replace(';','',$farmacia['telefono'])}} -
                        codice {{$farmacia['codice']}} - p.iva {{$farmacia['piva']}}<br>
                        @if( !$loop->last)
                            <br>
                        @endif
                    @endif
                @endforeach
            </p>
        @endif
    </div>
@endif
