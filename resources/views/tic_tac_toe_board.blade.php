<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            border-collapse: collapse;
            margin: auto;
            margin-top: 50px;
        }
        td {
            width: 100px;
            height: 100px;
            text-align: center;
            font-size: 50px;
            border: 1px solid black;
        }
    </style>
</head>
<body>
    <table>
        @foreach (['A', 'B', 'C'] as $row)
            <tr>
                @for ($col = 1; $col <= 3; $col++)
                    <td>{{ $board[$row . $col] }}</td>
                @endfor
            </tr>
        @endforeach
    </table>
</body>
</html>
