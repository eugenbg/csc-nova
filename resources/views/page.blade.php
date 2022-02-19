<x-layout>
    @section('title', $page->title)
    <div class="container article">
        <div class="row justify-content-center align-items-stretch">
            <div class="col-lg-8 order-lg-2 px-lg-5">
                <h1>{{$page->title}}</h1>
                <div>
                    {!! $page->content !!}
                </div>
            </div>
        </div>
    </div>

</x-layout>
