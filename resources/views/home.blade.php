<x-layout>
    @section('title', 'VmWare Walk Throughs')
    <div class="section-latest">
        <div class="container">
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

                    <div class="row">
                        <div class="col-12">
                            <h2 class="section-title">Vmware Problems With Solutions</h2>
                        </div>
                        @foreach($smallPosts as $smallPost)
                        <div class="col-12">
                            <div class="post-entry horizontal d-flex">
                                <div class="text">
                                    <h2><a href="{{$smallPost->slug}}">{{$smallPost->keyword}}</a></h2>
                                    <div class="meta">
                                        <span>{{$smallPost->createdAtFormatted()}}</span>
                                    </div>
                                    <p>{{$smallPost->getExcerpt(15)}}</p>

                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <h3><a href="/vmware?page=2">More VmWare Notes</a></h3>
                </div>
                <div class="col-md-3">
                    <x-subscribe-component/>
                </div>
            </div>
        </div>
    </div>

</x-layout>
