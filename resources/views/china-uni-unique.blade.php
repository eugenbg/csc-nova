<x-layout>
    @section('title', $uni->name . ' CSC Scholarship Application Process in 2022')

    <section class="section">
        <div class="container">
            <div class="columns">
                <div class="column is-offset-2 is-8">
                    <div class="box">
                        <div class="content">
                            @if($uni->image)
                                <img style="float: left; width: 40%" class="mr-6 mb-2" src="/images/{{$image->local_path}}" width="40%" alt="{{$uni->name}}"/>
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

    <section class="section">
        <div class="container">
            <div class="columns">
                <div class="column is-offset-2 is-8">
                    <div class="box">
                        <div>
                            <h4>Programs and courses available at {{$uni->abbr}} sponsored by the China Scholarship Council</h4>
                            @php
                                $i = 1;
                            @endphp
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                @foreach ($programs as $type => $programList)
                                    @if(count($programList))
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link {{$i==1 ? 'active' : ''}}" id="{{$type}}-tab" data-bs-toggle="tab" data-bs-target="#{{$type}}" type="button" role="tab" aria-controls="home" aria-selected="true">{{$type}} Programs</button>
                                        </li>
                                    @endif
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                            </ul>
                            <div class="tab-content p-3 card border-top-0 overflow-auto" style="max-height: 500px" id="myTabContent">
                                @php
                                    $i = 1;
                                @endphp
                                @foreach ($programs as $type => $programList)
                                    @if(count($programList))
                                        <div class="container tab-pane fade show {{$i==1 ? 'active' : ''}}" id="{{$type}}" role="tabpanel" aria-labelledby="{{$type}}-tab">
                                            <div class="row">
                                                <h2>{{$type}} Programs at {{$uni->name}}</h2>
                                                @foreach ($programList as $programName => $concreteCourses)
                                                    @foreach ($concreteCourses as $course)
                                                        <div class="col-5 m-2">
                                                            <div class="card">
                                                                <div class="card-header">{{$programName}} (taught in {{$course->language}})</div>
                                                                <div class="card-body">Price per year: {{$course->price}}</div>
                                                                <div class="card-footer">Duration: {{$course->years}} years</div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(count($campusImages))
        <section class="section">
            <div class="container">
                <div class="columns">
                    <div class="column is-offset-2 is-8">
                        <div class="box">
                            <h2>Campus Photos</h2>
                            <div id="carousel-campus" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($campusImages as $image)
                                        <button type="button" data-bs-target="#carousel-campus"
                                                data-bs-slide-to="{{$i}}" {{$i == 1 ? 'class=active aria-current=true' : ''}}>
                                        </button>
                                        @php
                                            $i++;
                                        @endphp
                                    @endforeach

                                </div>
                                <div class="carousel-inner">
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($campusImages as $image)
                                        <div class="carousel-item {{$i == 0 ? 'active' : ''}}">
                                            <div style="background-image:url(/images/{{$image->local_path}})"></div>
                                        </div>
                                        @php
                                            $i++;
                                        @endphp
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-campus" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carousel-campus" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if(count($dormImages))
        <section class="section">
            <div class="container">
                <div class="columns">
                    <div class="column is-offset-2 is-8">
                        <div class="box">
                            <h2>Dorm Photos</h2>
                            <div id="carousel-dorm" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($dormImages as $image)
                                        <button type="button" data-bs-target="#carousel-dorm"
                                                data-bs-slide-to="{{$i}}" {{$i == 1 ? 'class=active aria-current=true' : ''}}>
                                        </button>
                                        @php
                                            $i++;
                                        @endphp
                                    @endforeach

                                </div>
                                <div class="carousel-inner">
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($dormImages as $image)
                                        <div class="carousel-item {{$i == 0 ? 'active' : ''}}">
                                            <div style="background-image:url(/images/{{$image->local_path}})"></div>
                                        </div>
                                        @php
                                            $i++;
                                        @endphp
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-dorm" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carousel-dorm" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if(count($dormImages))
        <section class="section">
            <div class="container">
                <div class="columns">
                    <div class="column is-offset-2 is-8">
                        <div class="box">
                            <div id="dorms">
                                <h2>Available Dorm Rooms</h2>
                                <table class="table table-hover table-success table-striped">
                                    <thead>
                                    <tr>
                                        <th scope="col">Room Type</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Toilet</th>
                                        <th scope="col">Bathroom</th>
                                        <th scope="col">Internet</th>
                                        <th scope="col">Air Conditioner</th>
                                        <th scope="col">Comments</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($dorms as $dorm)
                                        <tr>
                                            <td>{{$dorm->type}}</td>
                                            <td>{{$dorm->rate}}</td>
                                            <td>{{$dorm->toilet}}</td>
                                            <td>{{$dorm->bathroom}}</td>
                                            <td>{{$dorm->internet}}</td>
                                            <td>{{$dorm->airConditioner}}</td>
                                            <td>{{$dorm->comments}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
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
        const myCarousel = document.querySelector('#uni-photos')
        const carousel = new bootstrap.Carousel(myCarousel)
    </script>


</x-layout>
