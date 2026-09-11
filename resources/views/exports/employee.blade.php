<table>
    <thead>
    <tr>
        <th>{{ __('Employee ID') }}</th>
        <th>{{ __('Name') }}</th>
        <th>{{ __('Gender') }}</th>
        <th>{{ __('DOJ') }}</th>
        <th>{{ __('Employment Type') }}</th>
        <th>{{ __('Unit') }}</th>
        <th>{{ __('Job') }}</th>
        <th>{{ __('Grade') }}</th>
        <th>{{ __('Company') }}</th>
        <th>{{ __('Location') }}</th>
        <th>{{ __('Group Company') }}</th>
        <th>{{ __('Email') }}</th>
        <th>{{ __('Goals Menu') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $row)
        <tr>
            <td>{{ $row->employee_id }}</td>
            <td>{{ $row->fullname }}</td>
            <td>{{ $row->gender }}</td>
            <td>{{ $row->date_of_joining }}</td>
            <td>{{ $row->employee_type }}</td>
            <td>{{ $row->unit }}</td>
            <td>{{ $row->designation }}</td>
            <td>{{ $row->job_level }}</td>
            <td>{{ $row->contribution_level_code }}</td>
            <td>{{ $row->office_area }}</td>
            <td>{{ $row->group_company }}</td>
            <td>{{ $row->email }}</td>
            <td>{{ isset($row->access_menu['goals']) && $row->access_menu['goals'] == 1 ? 'yes' : 'no' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
