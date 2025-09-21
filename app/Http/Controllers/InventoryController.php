<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        return view('Inventory.inventory_home');
    }

    public function stocks()
    {
        return view('Inventory.inventory_stocks');
    }

    public function orders()
    {
        return view('Inventory.inventory_orders');
    }

    public function reports()
    {
        return view('Inventory.inventory_reports');
    }

    public function account()
    {
        return view('Inventory.inventory_account');
    }
}
