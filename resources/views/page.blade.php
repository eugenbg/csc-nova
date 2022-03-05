<x-layout>
    @section('title', $page->title)
    <div class="container article">
        <div class="row justify-content-center align-items-stretch">
            <div class="col-lg-10">
                <h1 class="text-center mb-3">{{$page->title}}</h1>
                <div>
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>

</x-layout>
