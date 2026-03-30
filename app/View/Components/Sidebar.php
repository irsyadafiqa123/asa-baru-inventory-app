<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    public $itemsNav = [['name' => 'Dashboard', 'icon' => 'ri-dashboard-line', 'href' => '/', 'route' => 'dashboard'], ['name' => 'Data Barang', 'icon' => 'ri-box-3-line', 'href' => '/barang', 'route' => 'barang'], ['name' => 'Barang Masuk', 'icon' => 'ri-inbox-archive-line', 'href' => '/barang_masuk', 'route' => 'barang_masuk'], ['name' => 'Barang Keluar', 'icon' => 'ri-inbox-unarchive-line', 'href' => '/barang_keluar', 'route' => 'barang_keluar'], ['name' => 'Laporan', 'icon' => 'ri-table-line', 'href' => '/laporan', 'route' => 'laporan']];

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.sidebar', $this->itemsNav);
    }
}
