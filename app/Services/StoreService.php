<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Pagination\LengthAwarePaginator;

class StoreService
{
    public function listStores(int $perPage = 15): LengthAwarePaginator
    {
        return Store::with('user')->paginate($perPage);
    }

    public function createStore(array $data): Store
    {
        $data['approval_status'] = 'pending';
        return Store::create($data);
    }

    public function updateStore(Store $store, array $data): Store
    {
        $store->update($data);
        return $store;
    }

    public function deleteStore(Store $store): void
    {
        $store->delete();
    }
}