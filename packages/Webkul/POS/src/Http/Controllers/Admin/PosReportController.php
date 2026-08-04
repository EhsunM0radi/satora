<?php

namespace Webkul\POS\Http\Controllers\Admin;

use Webkul\Admin\Http\Controllers\Controller;

class PosReportController extends Controller
{
    public function index()
    {
        return view('pos::admin.index');
    }

    public function show(int $id)
    {
        return view('pos::admin.show', compact('id'));
    }

    public function store()
    {
        // Implementation pending
    }

    public function update(int $id)
    {
        // Implementation pending
    }

    public function destroy(int $id)
    {
        // Implementation pending
    }

    public function void(int $id)
    {
        // Void order implementation
    }

    public function sales()
    {
        return view('pos::admin.reports.sales');
    }

    public function cashier()
    {
        return view('pos::admin.reports.cashier');
    }

    public function products()
    {
        return view('pos::admin.reports.products');
    }

    public function payments()
    {
        return view('pos::admin.reports.payments');
    }

    public function inventory()
    {
        return view('pos::admin.reports.inventory');
    }
}
