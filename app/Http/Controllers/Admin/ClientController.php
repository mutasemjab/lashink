<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware($this->perm('client-table'))->only(['index', 'show']);
        $this->middleware($this->perm('client-add'))->only(['create', 'store']);
        $this->middleware($this->perm('client-edit'))->only(['edit', 'update']);
        $this->middleware($this->perm('client-delete'))->only(['destroy']);
    }

    public function index(Request $request)
    {
        $clients = Client::when($request->search, fn($q, $s) =>
                $q->where(fn($q2) => $q2
                    ->where('name', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%")
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.client.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.client.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Client::create($data);

        return redirect()->route('admin.client.index')->with('success', __('messages.saved_successfully'));
    }

    public function edit(Client $client)
    {
        return view('admin.client.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $this->validated($request, $client->id);
        $client->update($data);

        return redirect()->route('admin.client.index')->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return back()->with('success', __('messages.deleted_successfully'));
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'name'       => 'required|string|max:200',
            'phone'      => 'required|string|max:30|unique:clients,phone,' . $id,
            'phone2'     => 'nullable|string|max:30',
            'gender'     => 'required|in:female,male',
            'birthdate'  => 'nullable|date',
            'address'    => 'nullable|string|max:500',
            'source'     => 'nullable|string|max:100',
            'notes'      => 'nullable|string|max:2000',
        ]);
        $data['is_blocked'] = $request->boolean('is_blocked');

        return $data;
    }
}
