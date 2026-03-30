<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    // tampilkan data
    public function index()
    {
        $supplier = Supplier::all();
        return view('supplier.index', compact('supplier'));
    }

    // tampilkan form
    public function create()
    {
        return view('supplier.create');
    }

    // simpan data
    public function store(Request $request)
    {
        Supplier::create($request->all());
        return redirect('/supplier');
    }
}