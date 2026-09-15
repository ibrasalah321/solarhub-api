<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStorePayoutRequest;
use App\Models\StorePayout;

class StorePayoutController extends Controller
{
    public function index() { return response()->json(StorePayout::with(['order', 'store'])->get()); }
    public function store(StoreStorePayoutRequest $request) { return response()->json(StorePayout::create($request->validated()), 201); }
    public function show($id) { return response()->json(StorePayout::with(['order', 'store'])->findOrFail($id)); }
    public function update(StoreStorePayoutRequest $request, $id) { $payout = StorePayout::findOrFail($id); $payout->update($request->validated()); return response()->json($payout); }
    public function destroy($id) { StorePayout::findOrFail($id)->delete(); return response()->json(['message' => 'Store payout deleted successfully']); }
}