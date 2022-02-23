<section class="section mt-5 p-2">
    <div class="container border">
        <h3>Programs and courses available at {{$uni->abbr}} sponsored by the China Scholarship Council</h3>
        @php
            $i = 1;
        @endphp
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            @foreach ($programs as $type => $programList)
                @if(count($programList))
                    <li class="nav-item" role="presentation">
                        <a class="nav-link {{$i==1 ? 'active' : ''}}" id="{{$type}}-tab" data-toggle="tab" href="#{{$type}}" role="tab" aria-controls="{{$type}}" aria-selected="true">{{$type}} Programs</a>
                    </li>
                    @php
                        $i++;
                    @endphp
                @endif
            @endforeach

        </ul>

        <div class="tab-content" id="myTabContent">
            @php
                $i = 1;
            @endphp
            @foreach ($programs as $type => $programList)
                @if(count($programList))
                    <div class="container tab-pane fade show {{$i==1 ? 'active' : ''}}" id="{{$type}}" role="tabpanel" aria-labelledby="{{$type}}-tab">
                        <div class="row">
                            @foreach ($programList as $programName => $concreteCourses)
                                @foreach ($concreteCourses as $course)
                                    <div class="col-4 mt-2 mb-2">
                                        <div class="card">
                                            <div class="card-header">{{$getProgramTypeLabel($type)}}{{$programName}} (taught in {{$course->language}})</div>
                                            <div class="card-body">Price per year: {{$course->price}}</div>
                                            <div class="card-footer">Duration: {{$course->years}} years</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    @php
                        $i++;
                    @endphp
                @endif
            @endforeach
        </div>
    </div>
</section>
