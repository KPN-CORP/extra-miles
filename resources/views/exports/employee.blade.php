<table>
    <thead>
    <tr>
        <th>Employee ID</th>
        <th>Name</th>
        <th>Gender</th>
        <th>DOJ</th>
        <th>Employment Type</th>
        <th>Unit</th>
        <th>Job</th>
        <th>Grade</th>
        <th>Company</th>
        <th>Location</th>
        <th>Group Company</th>
        <th>Email</th>
        <th>Menu Goals</th>
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
