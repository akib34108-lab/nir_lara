@extends('layouts.master')
@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">Client List</h4>
                        </div>
                        <div class="col-md-4 justify-content-end d-flex">
                            @can('clients-create')
                                <a href="{{ route('clients.create') }}"
                                   class="btn btn-primary btn-fw">
                                    Add Client
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Product</th>
                                    <th>Sale</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clients as $client)
                                    <tr>
                                        <td>{{ $client->name }}</td>
                                        <td>{{ $client->company }}</td>
                                        <td>{{ $client->email }}</td>
                                        <td>{{ $client->status }}</td>
                                        <td>
                                            @can('clients-view')
                                                <a href="{{ route('clients.show', $client->id) }}"
                                                   class="btn btn-info btn-sm">
                                                    View
                                                </a>
                                            @endcan
                                            @can('clients-edit')
                                                <a href="{{ route('clients.edit', $client->id) }}"
                                                   class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>
                                            @endcan
                                            @can('clients-delete')
                                                <form action="{{ route('clients.destroy', $client->id) }}"
                                                      method="POST"
                                                      style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure you want to delete this client?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection