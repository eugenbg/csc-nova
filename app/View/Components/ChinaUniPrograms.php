<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ChinaUniPrograms extends Component
{
    public $uni;
    public $programs;

    /**
     * Create a new component instance.
     *
     * @param $uni
     * @param $programs
     */
    public function __construct($uni, $programs)
    {
        $this->uni = $uni;
        $this->programs = $programs;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.china-uni-programs');
    }

    public function getProgramTypeLabel($type)
    {
        switch ($type) {
            case 'Doctoral':
                return 'Doctor of ';
            case 'No-Degree':
            case 'Associate':
                return '';
        }

        return $type . ' of ';
    }
}
