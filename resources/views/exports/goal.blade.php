<table>
    <thead>
    <tr>
        <th>{{ __('Employee ID') }}</th>
        <th>{{ __('Employee Name') }}</th>
        <th>{{ __('Category') }}</th>
        <th>{{ __('KPI') }}</th>
        <th>{{ __('Target') }}</th>
        <th>{{ __('Uom') }}</th>
        <th>{{ __('Weightage') }}</th>
        <th>{{ __('Type') }}</th>
        <th>{{ __('Form Status') }}</th>
        <th>{{ __('Approval Status') }}</th>
        <th>{{ __('Current Approver') }}</th>
        <th>{{ __('Current Approver ID') }}</th>
        <th>{{ __('Initiated By') }}</th>
        <th>{{ __('Initiated By ID') }}</th>
        <th>{{ __('Period') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($goals as $row)
        @php
            $formData = json_decode($row->goal->form_data, true);
        @endphp
        @if ($formData)
            @foreach ($formData as $item)
                <tr>
                    <td>{{ $row->employee_id }}</td>
                    <td>{{ $row->employee->fullname }}</td>
                    <td>{{ $row->goal->category }}</td>
                    <td>{{ $item['kpi'] }}</td>
                    <td>{{ $item['target'] }}</td>
                    <td>{{ $item['uom']==='Other' ? $item['custom_uom'] : $item['uom'] }}</td>
                    <td>{{ $item['weightage'] }}</td>
                    <td>{{ $item['type'] }}</td>
                    <td>{{ $row->goal->form_status }}</td>
                    <td>{{ $row->status=='Pending'? ($row->sendback_to ? 'Waiting For Revision' : ($row->goal->form_status=='Draft'?'Not Started':'Waiting For Approval')) : $row->status }}</td>
                    <td>{{ $row->status=='Sendback' && $row->sendback_to == $row->employee_id || $row->goal->form_status=='Draft' ? '-' : $row->manager->fullname }}</td>
                    <td>{{ $row->manager->employee_id }}</td>
                        <td>{{ $row->initiated ? $row->initiated->name : $row->employee->fullname }}</td>
                    <td>{{ $row->initiated ? $row->initiated->employee_id : $row->employee->employee_id }}</td>
                    <td>{{ $row->goal->period }}</td>
                </tr>
            @endforeach
        @endif
    @endforeach
    </tbody>
</table>
