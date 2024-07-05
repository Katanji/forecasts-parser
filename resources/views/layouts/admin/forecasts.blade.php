@extends('adminlte::page')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Forecasts</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Teams</th>
                    <th>Sport Type</th>
                    <th>Prediction</th>
                    <th>Last Results</th>
                    <th>Profit</th>
                    <th>Coefficient</th>
                    <th>Explanation</th>
                    <th>Author</th>
                    <th>Created At</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->teams }}</td>
                        <td>{{ $item->sport_type }}</td>
                        <td>{{ $item->prediction }}</td>
                        <td>{{ $item->last_results }}</td>
                        <td>{{ $item->profit }}</td>
                        <td>{{ $item->coefficient }}</td>
                        <td>{{ $item->explanation }}</td>
                        <td>{{ $item->author }}</td>
                        <td>{{ $item->created_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
