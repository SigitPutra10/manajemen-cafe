<?php

namespace App\View\Components;

use App\Models\Setting;
use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    public string $cafeName;

    public function __construct(public string $title = 'Panel Admin')
    {
        $this->cafeName = Setting::query()->value('cafe_name') ?: config('app.name');
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.admin');
    }
}
