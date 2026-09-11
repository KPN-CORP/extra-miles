<table>
    <thead>
    <tr>
        <th>{{ __('Employee ID') }}</th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Gender') }}</th>
        <th>{{ __('Email') }}</th>
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
