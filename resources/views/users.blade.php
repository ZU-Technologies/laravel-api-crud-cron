<!DOCTYPE html>
<html>
<head>
    <title>Laravel Pagination</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h1>Laravel Pagination </h1>

    <table class="table table-bordered data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->descrpition }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">There are no users.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!--
        You can use Tailwind CSS Pagination as like here:
        {!! $users->withQueryString()->links() !!}
    -->

    {!! $users->withQueryString()->links('pagination::bootstrap-5') !!}
</div>

</body>

</html>
