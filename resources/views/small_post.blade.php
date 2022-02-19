<x-layout>
    @section('title', $smallPost->title)
    <div class="featured-post single-article">
        <div class="container">
            <div class="post-slide single-page">
                <div class="text-wrap">
                    <h2>{{$smallPost->title}}</h2>
                    <div class="meta">
                        <span>{{$smallPost->createdAtFormatted()}}</span>
                    </div>
                </div>
            </div> <!-- .post-slide -->
        </div>
    </div>

    <div class="container article">
        <div class="row justify-content-center align-items-stretch">
            <div class="col-lg-8 order-lg-2 px-lg-5">
                <h3>Problem</h3>
                {!! $smallPost->question_formatted !!}
                <h3>Solution</h3>
                {!! $smallPost->answer_formatted !!}
            </div>

            <x-share-component/>

            <div class="col-lg-3 mb-5 mb-lg-0 order-lg-3">
                <x-subscribe-component/>
            </div>
        </div>

        <x-small-post-interlinking-component :links="$smallPost->links"/>
    </div>


</x-layout>
