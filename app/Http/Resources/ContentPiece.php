<?php

namespace App\Http\Resources;


class ContentPiece extends AbstractResource
{
    public $type;
    public $content;

    public function toString()
    {
        return $this->content;
    }

    public function getContent()
    {
        if($this->isHeading()) {
            return "\n--------------\n" . $this->content . "\n--------------\n";
        }

        if($this->type == 'p') {
            return "\n" . $this->content;
        }

        if($this->type == 'li') {
            return "\n- " . $this->content;
        }
    }

    public function isHeading()
    {
        return strpos($this->type, 'h') !== false;
    }
}
