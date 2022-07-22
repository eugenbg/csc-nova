<x-layout>
    @php /** @var \App\Models\GeneratedPost $post */ @endphp


    <script>

        function choose(id) {
            jQuery.get( '/choose/' + id)
        }

    </script>
    <div class="container article">
        @foreach($pairs as $pair)
            <h2>{{$pair->id}}</h2> <a href="afdd" onclick="choose({{$pair->id}}); return false;">CHOOSE</a>
            <div class="row mb-5 border-2">
                <div class="col-lg-6">
                    {{$pair->content}}
                </div>
                <div class="col-lg-6">
                    {{$pair->rewritten}}
                </div>
            </div>
        @endforeach
    </div>


</x-layout>
