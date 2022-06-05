<x-layout>
    @section('title', 'IMMERSV: all about virtual reality discovery and advertising')
    <div class="section-latest">
        <div class="container">
            <div class="row gutter-v1 align-items-stretch">
                <div class="col-12">
                    <h1 class="section-title">IMMERSV VR Experience</h1>
                </div>
                <div class="col-md-9 pr-md-5">
                    <div class="row mb-5 ml-1 mr-1 align-content-center">
                        <p>We write about virtual reality discovery and advertising and how game developers are using Virtual Reality (VR) to design games.</p>
                        <p>Main output of our blog is guidance in the processes of setting up and operating social VR communities, collaboration and matchmaking workspaces.</p>
                        <p>Demand for our virtual world blogging arose from scarcity of such content after the WaveVR platform launch in August 2017 followed by drop and absence of promotion, despite the fact that Virtual Reality can lead to self-employment, new skills and career growth.</p>
                        <p>Join us in our journey!</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <x-subscribe-component/>
                </div>
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
