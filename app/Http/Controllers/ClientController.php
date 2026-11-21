<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::withCount('projects')->latest()->paginate(10);
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $nextId = (Client::withTrashed()->max('id') ?? 0) + 1;
        $nextClientCode = 'CL-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        return view('clients.create', compact('nextClientCode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
            {
                $validatedData = $request->validate([
                    'client_code'   => 'required|unique:clients,client_code',
                    'client_type'   => 'required|in:1,2,3,4',
                    'name'          => 'required',
                    'company_name'  => 'nullable',
                    'email'         => 'nullable|email',
                    'phone'         => 'required',
                    'alternate_phone'=> 'nullable',
                    'website'       => 'nullable|url',
                    'tax_number'    => 'nullable',
                    'address'       => 'nullable',
                    'city'          => 'nullable',
                    'state'         => 'nullable',
                    'country'       => 'nullable',
                    'postal_code'   => 'nullable',
                    'notes'         => 'nullable',
                    'status'        => 'required|in:1,2,3,4',
        ]);

        Client::create($validatedData);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validatedData = $request->validate([
            'client_code' => 'required|unique:clients,client_code,' . $client->id,
            'client_type' => 'required|in:1,2,3,4',
            'name' => 'required',
            'company_name' => 'nullable',
            'email' => 'nullable|email',
            'phone' => 'required',
            'alternate_phone' => 'nullable',
            'website' => 'nullable|url',
            'tax_number' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'state' => 'nullable',
            'country' => 'nullable',
            'postal_code' => 'nullable',
            'notes' => 'nullable',
            'status' => 'required|in:1,2,3,4',
        ]);

        $client->update($validatedData);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
