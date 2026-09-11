<table>
    <thead>
    <tr>
        <th>{{ __('Employee ID') }}</th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Designation') }}</th>
        <th>{{ __('Business Unit') }}</th>
        <th>{{ __('Company') }}</th>
        <th>{{ __('Location') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $row)
        <tr>
            <td>{{ $row->employee_id }}</td>
            <td>{{ $row->employee->fullname }}</td>
            <td>{{ $row->employee->designation_name }}</td>
            <td>{{ $row->employee->group_company }}</td>
            <td>{{ $row->employee->contribution_level_code }}</td>
            <td>{{ $row->employee->office_area }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
