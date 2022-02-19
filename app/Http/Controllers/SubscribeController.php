<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;

class SubscribeController
{
    public function subscribe(Request $request)
    {
        $request->session()->flash('success', 'You have subscribed');
        return redirect('/');
    }

}
