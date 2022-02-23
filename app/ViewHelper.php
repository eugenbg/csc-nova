<?php

namespace App;

class ViewHelper {

    public static function currentUrl()
    {
        return request()->fullUrl();
    }
}
