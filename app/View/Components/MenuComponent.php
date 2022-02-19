<?php

namespace App\View\Components;

use Illuminate\Http\Request;
use Illuminate\View\Component;
use OptimistDigital\MenuBuilder\Models\Menu;
use OptimistDigital\MenuBuilder\Models\MenuItem;

class MenuComponent extends Component
{
    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new component instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $menu = Menu::query()
            ->first();

        return view('components.menu-component', ['menu' => $menu]);
    }

    public function isActive(MenuItem $menuItem)
    {
        return $this->request->path() == $menuItem->url;
    }

    public function hasChildren(MenuItem $menuItem)
    {
        return $menuItem->children->count();
    }
}
