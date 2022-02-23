e
<x-layout>
    @section('title', $category->title)
    <div class="section-latest">
        <div class="container">
            <div class="row gutter-v1 align-items-stretch">
                <div class="col-12">
                    <h2 class="section-title">{{$category->title}}</h2>
                </div>
                <div class="col-md-9 pr-md-5">
                    <div class="row">
                        @foreach($category->posts as $post)
                            <div class="col-12">
                                <div class="post-entry horizontal d-flex">
                                    <div class="media">
                                        @if($post->image)<a href="/{{$post->slug}}"><img  src="{{$post->image}}" class="img-fluid" ></a>@endif
                                    </div>
                                    <div class="text">
                                        <h2><a href="/{{$post->slug}}">{{$post->title}}</a></h2>
                                        <div class="meta">
                                            <span>{{$post->createdAtFormatted()}}</span>
                                            <span>&bullet;</span>
                                            <span>{{$post->minutesNeededToRead()}}</span>
                                        </div>
                                        <p>{{$post->getExcerpt()}}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-3">
                    <x-subscribe-component/>
                </div>
            </div>
        </div>
    </div>


</x-layout>
