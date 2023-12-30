<!DOCTYPE html>
<html lang="en">
<head>
    <title>New Bet</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<table>
    <tr>
        <th>Teams</th>
        <td>{{ $data['teams'] }}</td>
    </tr>
    <tr>
        <th>Sport Type</th>
        <td>{{ $data['sport_type'] }}</td>
    </tr>
    <tr>
        <th>Prediction</th>
        <td>{{ $data['prediction'] }}</td>
    </tr>
    <tr>
        <th>Date</th>
        <td>{{ $data['date'] }}</td>
    </tr>
    <tr>
        <th>Last Results</th>
        <td>{{ $data['last_results'] }}</td>
    </tr>
    <tr>
        <th>Profit</th>
        <td>{{ $data['profit'] }}</td>
    </tr>
    <tr>
        <th>Coefficient</th>
        <td>{{ $data['coefficient'] }}</td>
    </tr>
    <tr>
        <th>Explanation</th>
        <td>{{ $data['explanation'] }}</td>
    </tr>
    <tr>
        <th>Author</th>
        <td><a href="{{$data['author_link']}}">{{ $data['author'] }}</a></td>
    </tr>
</table>
</body>
</html>
