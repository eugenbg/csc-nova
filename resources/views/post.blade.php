<x-layout>
    @section('title', $post->title)

    <div class="featured-post single-article">
        <div class="text-wrap p-2">
            <div class="meta-cat"><a href="/{{$post->category->slug}}">{{$post->category->title}}</a></div>
            <h2>{{$post->title}}</h2>
            <div class="meta">
                <span>{{$post->createdAtFormatted()}}</span>
                <span>&bullet;</span>
                <span>{{round(strlen($post->content) / 1500)}} minutes read</span>
            </div>
        </div>
    </div>


    <div class="container article">
        <div class="row justify-content-center align-items-stretch">

            <article class="col-lg-8 order-lg-2 px-lg-5">
                {!! $post->content !!}
            </article>

            <x-share-component/>

            <div class="col-lg-3 mb-5 mb-lg-0 order-lg-3">
                <x-subscribe-component/>
            </div>
        </div>
    </div>


</x-layout>
