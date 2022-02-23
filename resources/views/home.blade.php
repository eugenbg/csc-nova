<x-layout>
    @section('title', 'China Scholarship Council (CSC)')
    <div class="section-latest">
        <div class="container">
            <div class="row gutter-v1 align-items-stretch">
                <div class="col-12">
                    <h1 class="section-title">China Scholarship Council (CSC)</h1>
                </div>
                <div class="col-md-9 pr-md-5">
                    <div class="row mb-5">
                        {!! $mainPost->content !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <x-subscribe-component/>
                </div>
            </div>

            <div class="row">
                <h1 style="text-align: center">Universities Offering CSC Scholarships</h1>
            </div>

            <div class="row">

                @foreach ($unis as $uni)
                    <div class="col-4 p-1">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><strong>{{$uni->abbr}}</strong> - {{$uni->name}}</h5>
                                <span>Region: {{$uni->getRegion()}}</span>
                            </div>
                            <div class="card-body">
                                <a href="{{$uni->link}}">{{$uni->name}} scholarships</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>


            <div class="row gutter-v1 align-items-stretch">
                <div class="col-12">
                    <h2 class="section-title">Latest blog posts</h2>
                </div>
                <div class="col-md-9 pr-md-5">
                    <div class="row mb-5">
                        @foreach($posts as $post)
                        <div class="col-md-6">
                            <div class="post-entry">
                                <div class="media">
                                    <a href="{{$post->slug}}"><img src="{{$post->image}}" class="img-fluid"></a>
                                </div>
                                <div class="text">
                                    <div class="meta-cat"><a href="{{$post->category->slug}}">{{$post->category->title}}</a></div>
                                    <h2><a href="{{$post->slug}}">{{$post->title}}</a></h2>
                                    <div class="meta">
                                        <span>{{$post->createdAtFormatted()}}</span>
                                        <span>&bullet;</span>
                                        <span>5 mins read</span>
                                    </div>
                                    {{$post->getExcerpt()}}

                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layout>
