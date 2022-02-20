<style>
    .card-img-top {
        width: 100%;
        height: 15vw;
        object-fit: cover;
    }

    .card {
        padding: 0;
    }
</style>

<section class="section">
    <div class="container">
        <div class="columns">
            <div class="column is-offset-2 is-8">
                <div class="box">
                    <h3>Other Chinese Universities funded by China Scholarship Council</h3>
                    <div class="row">
                        @foreach ($links as $link)
                            <div class="col-4 p-1">
                                <div class="card">
                                    @if($link->linkedUni->image->local_path ?? null)
                                        <img alt="CSC Scholarships for {{$link->linkedUni->name}}" class="card-img-top"
                                             src="/images/{{$link->linkedUni->image->local_path}}"/>
                                    @endif

                                    <div class="card-body">
                                        <h5 class="card-title"><strong>{{$link->linkedUni->abbr}}</strong>
                                            - {{$link->linkedUni->name}}</h5>
                                    </div>
                                    <div class="card-body">
                                        <a href="{{$link->linkedUni->link}}">CSC Guide for {{$link->linkedUni->name}}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
