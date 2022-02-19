<ul class="js-clone-nav d-none d-lg-inline-block text-left site-menu">

    @foreach($menu->rootMenuItems() as $item)
        <li class="@if($hasChildren($item)) has-children @endif @if($isActive($item)) active @endif">
            <a href="/{{$item->url}}">{{$item->name}}</a>
            @if($item->children->count())
                <ul class="dropdown">
                    @foreach($item->children as $itemLevel2)
                        <li class="@if($hasChildren($itemLevel2)) has-children @endif @if($isActive($itemLevel2)) active @endif">
                            <a href="{{$itemLevel2->url}}">{{$itemLevel2->name}}</a>
                            @if($itemLevel2->children->count())
                                <ul class="dropdown">
                                    @foreach($itemLevel2->children as $itemLevel3)
                                        <li class="@if($isActive($itemLevel2)) active @endif">
                                            <a href="/{{$itemLevel3->url}}">{{$itemLevel3->name}}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </li>
    @endforeach
</ul>
