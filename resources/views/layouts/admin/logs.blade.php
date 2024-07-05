@extends('adminlte::page')

@section('title', 'Laravel Log')

@section('content_header')
    <h1>Logs</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="logTable" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Time</th>
                        <th>Env</th>
                        <th>Level</th>
                        <th>Message</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($logs as $log)
                        <tr class="{{ getRowClass($log['level']) }}">
                            <td>{{ $log['timestamp'] }}</td>
                            <td>{{ $log['environment'] }}</td>
                            <td>
                                <span class="badge {{ getBadgeClass($log['level']) }}">
                                    {{ strtoupper($log['level']) }}
                                </span>
                            </td>
                            <td>{{ $log['message'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@push('js')
    @vite(['resources/js/app.js'])
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new DataTable("#logTable", {
                perPageSelect: [10, 25, 50, 100],
                perPage: 50,
                sortable: true,
                searchable: true,
                fixedHeight: true,
                labels: {
                    placeholder: "Search...",
                    perPage: "{select} entries per page",
                    noRows: "No entries found",
                    info: "Showing {start} to {end} of {rows} entries",
                },
            });
        });
    </script>
@endpush

@php
    function getRowClass($level): string
    {
        return match ($level) {
            'error', 'critical', 'alert', 'emergency' => 'table-danger',
            'warning' => 'table-warning',
            'notice', 'info' => 'table-info',
            default => '',
        };
    }

    function getBadgeClass($level): string
    {
        return match ($level) {
            'error', 'critical', 'alert', 'emergency' => 'badge-danger',
            'warning' => 'badge-warning',
            'notice', 'info' => 'badge-info',
            default => 'badge-secondary',
        };
    }
@endphp
