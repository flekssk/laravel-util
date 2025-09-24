<?php

namespace FKS\Web\Http\Controllers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use FKS\Web\WebView;

class WebController
{
    public function index()
    {
        return view('web-view::web-view', [
            'path' => app(UrlGenerator::class)->to('/web-view'),
        ]);
    }

    public function execute(Request $request, WebView $tinker)
    {
        $validated = $request->validate([
            'code' => 'required',
        ]);

        return $tinker->execute($validated['code']);
    }
}
