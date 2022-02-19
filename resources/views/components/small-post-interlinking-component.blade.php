<div class="row mt-4">
    @foreach ($links as $link)
        <div class="col-4 p-1">
            <div class="card">
                <div class="card-body">
                    <a href="{{$link->linkedSmallPost->slug}}">{{$link->linkedSmallPost->keyword}}</a>
                </div>
                <div class="card-body">
                    {{$link->linkedSmallPost->getExcerpt(10)}}
                </div>
            </div>
        </div>
    @endforeach
</div>
