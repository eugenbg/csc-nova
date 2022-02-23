<x-layout>
    @section('title', $uni->name . ' CSC Scholarship Application Process in 2022')

    <div class="featured-post single-article">
        <div class="text-wrap p-2">
            <h1 class="section-title pl-2 pr-2">{{$uni->name . ' CSC Scholarship Application Process in 2022'}}</h1>
            <div class="meta">
                <span>5 minutes read</span>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="columns">
                <div class="column is-offset-2 is-8">
                    <div class="box">
                        <div class="content">
                            @if($uni->image)
                                <img style="float: left; width: 40%" class="mr-4 mb-3" src="/images/{{$image->local_path}}" width="40%" alt="{{$uni->name}}"/>
                            @endif
                            {!! $content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div>
        @include('uni-links', [
            'links' => $links,
        ])
    </div>

    <x-china-uni-programs
        :uni="$uni"
        :programs="$programs"
    />

    @if(count($campusImages))
        <section class="section">
            <div class="container">
                <div class="border p-2">
                    <h3>Campus photos</h3>
                    <div class="owl-carousel owl-theme">
                        @php
                            $i = 0;
                        @endphp
                        @foreach ($campusImages as $image)
                            <div class="item {{$i == 0 ? 'active' : ''}}">
                                <img src="/images/{{$image->local_path}}"/>
                            </div>
                            @php
                                $i++;
                            @endphp
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if(count($dormImages))
        <section class="section mt-2">
            <div class="container">
                <div class="border p-2">
                    <h3>Dorm photos</h3>
                    <div class="owl-carousel owl-theme">
                        @php
                            $i = 0;
                        @endphp
                        @foreach ($dormImages as $image)
                            <div class="item {{$i == 0 ? 'active' : ''}}">
                                <img src="/images/{{$image->local_path}}"/>
                            </div>
                            @php
                                $i++;
                            @endphp
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($scholarships->count())
        <section class="section">
            <div class="container">
                <div class="columns">
                    <div class="column is-offset-2 is-8">
                        <div class="box">
                            <div id="scholarships">
                                <h2>Additional scholarships available for {{$uni->name}}</h2>
                                <div class="row">
                                    @foreach ($scholarships as $scholarship)
                                        <div class="card text-white bg-success m-1" style="max-width: 18rem;">
                                            <div class="card-header">{{$scholarship->name}}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <script>
        $('.owl-carousel').owlCarousel({
            margin:10,
            loop:true,
            autoWidth:true,
            items:4
        })
    </script>


</x-layout>
