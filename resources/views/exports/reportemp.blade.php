<table>
    <thead>
    <tr>
        <th>Employee ID</th>
        <th>Name</th>
        <th>Gender</th>
        <th>Email</th>
    </tr>
    </thead>
    <tbody>
    @foreach($employees as $employee)
        <tr>
            <td>{{ $employee->employee_id }}</td>
            <td>{{ $employee->fullname }}</td>
            <td>{{ $employee->gender }}</td>
            <td>{{ $employee->email }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
