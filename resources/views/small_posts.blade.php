<x-layout>
    @section('title', 'VmWare notes')
    <div class="section-latest">
        <div class="container">
            <div class="row gutter-v1 align-items-stretch">
                <div class="col-12">
                    <h2 class="section-title">VmWare Notes</h2>
                </div>
                <div class="col-md-9 pr-md-5">
                    <div class="row">
                        @foreach($smallPosts as $smallPost)
                            <div class="col-12">
                                <div class="post-entry horizontal d-flex">
                                    <div class="text">
                                        <h2><a href="/{{$smallPost->slug}}">{{ucfirst($smallPost->title)}}</a></h2>
                                        <div class="meta mb-0">
                                            <span>asked on {{$smallPost->createdAtFormatted()}}</span>
                                        </div>
                                        <p>{{$smallPost->getExcerpt(20)}}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div>
                        {{ $smallPosts->links() }}
                    </div>
                </div>
                <div class="col-md-3">
                    <x-subscribe-component/>
                </div>
            </div>
        </div>
    </div>


</x-layout>
