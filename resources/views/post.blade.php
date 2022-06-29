<x-layout>
    @php /** @var \App\Models\GeneratedPost $post */ @endphp

    @section('title', $post->title)

    <div class="featured-post single-article">
        <div class="text-wrap p-2">
            @if($post->category)
                <div class="meta-cat"><a href="/{{$post->category->slug}}">{{$post->category->title}}</a></div>
            @endif

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

            @if($debug)
                @foreach($post->chosenGeneratedPieces as $piece)
                    <div class="clearfix">
                        <h2>Generated heading: {{$piece->chosen_heading}}</h2>
                        <h2>Original heading: {{$piece->piece->heading}}</h2>
                        @if($piece->image)
                            <figure class="image image-style-align-left">
                                <img src="{{$piece->getImage()}}"/>
                            </figure>
                        @endif
                        <p>Generated content: {!! $piece->formatted_content !!}</p>
                        <p>Generated pre: <br/><pre>{{$piece->content}}</pre></p>
                        <p>Original content: <br/><pre>{{$piece->piece->content}}</pre></p>
                        <p>Generated Piece ID: <br/><pre>{{$piece->id}}</pre></p>
                    </div>
                @endforeach
            @else
                <article class="col-lg-8 order-lg-2 px-lg-5">
                    @foreach($post->chosenGeneratedPieces as $piece)
                        <div class="clearfix">
                            <h2>{{$piece->chosen_heading}}</h2>
                            @if($piece->image)
                                <figure class="image image-style-align-left">
                                    <img src="{{$piece->getImage()}}"/>
                                </figure>
                            @endif
                            <p>{!! $piece->formatted_content !!}</p>
                        </div>
                    @endforeach

                    <table class="table table-hover">
                        <tbody>
                            @if($post->keyword->additional_data['website'])
                                <tr>
                                    <td>Website</td>
                                    <td>
                                        <a rel="nofollow" target="_blank"
                                           href="{{$post->keyword->additional_data['website']}}">
                                            {{$post->keyword->additional_data['website']}}
                                        </a>
                                    </td>
                                </tr>
                            @endif
                            @if($post->keyword->additional_data['phone'])
                                <tr>
                                    <td>Phone Number</td>
                                    <td>{{$post->keyword->additional_data['phone']}}</td>
                                </tr>
                            @endif
                            @if($post->keyword->additional_data['address'])
                                <tr>
                                    <td>Address</td>
                                    <td>{{$post->keyword->additional_data['address']}}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </article>
            @endif


            <x-share-component/>

            <div class="col-lg-3 mb-5 mb-lg-0 order-lg-3">
                <x-subscribe-component/>
            </div>
        </div>
    </div>


</x-layout>
