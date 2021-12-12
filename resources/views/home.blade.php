@extends('main')
@push('head')
    <title>Luoghi.numerosamente.it | Luoghi in Italia</title>
    <meta name="description" content="Scopri informazioni e statistiche dei comuni e province italiane. ">
    <link rel="canonical" href="{{url("/")}}"/>
@endpush
@section('content')
    <div class="home justify-content-center d-flex flex-column text-center align-self-center h-100">
        <h1>Luoghi in Italia</h1>
        <input id="autoComplete" type="search" dir="ltr" spellcheck=false autocorrect="off" autocomplete="off"
               autocapitalize="off" maxlength="2048" tabindex="1">
        <h2 class="mt-4"> Alcune ricerche frequenti </h2>
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 ">
                <div class="rounded-2 rounded bg-white p-2">@foreach( $places as $nome => $url )
                        <a href="{{$url}}">{{$nome}}</a>
                    @endforeach</div>
            </div>
        </div>
    </div>



@endsection

@push('head')
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.6/dist/css/autoComplete.min.css">
@endpush

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/@tarekraafat/autocomplete.js@10.2.6/dist/autoComplete.min.js"></script>

    <script>
        var url = "{{url('api/suggester')}}"
        var config = {
            selector: "#autoComplete",
            placeHolder: "Cerca ...",
            data: {
                src: async (query) => {
                    try {
                        // Fetch Data from external Source
                        const source = await fetch(`${url}?search=${query}`);
                        // Data is array of `Objects` | `Strings`
                        const data = await source.json();

                        return data;
                    } catch (error) {
                        return error;
                    }
                },
                // Data 'Object' key to be searched
                keys: ["nome"]
            },
            resultsList: {
                element: (list, data) => {
                    if (!data.results.length) {
                        // Create "No Results" message element
                        const message = document.createElement("div");
                        // Add class to the created element
                        message.setAttribute("class", "no_result");
                        // Add message text content
                        message.innerHTML = `<span>Nessun risultato per "${data.query}"</span>`;
                        // Append message element to the results list
                        list.prepend(message);
                    }
                },
                noResults: true,
            },
            resultItem: {
                highlight: {
                    render: true
                }
            }
        };
        const autoCompleteJS = new autoComplete(config);
        autoCompleteJS.input.addEventListener("selection", function (event) {

            document.location.href = event.detail.selection.value.url;
        });
    </script>
@endsection
