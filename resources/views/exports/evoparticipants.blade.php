<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Participant</th>
            <th>BU</th>
            <th>Location</th>
            <th>Submitted At</th>
            <th>WhatsApp</th>
            <th>#</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($participants as $p)
            @php
                $formData = json_decode($p->form_data, true);
                $country = $formData['countryCode'] ?? '+62';
                $number = preg_replace('/[^0-9]/', '', $formData['whatsapp_number'] ?? '');
                $phone = $country . $number;
                $question = $formData['question_2'] ?? [];
                $questionList = is_array($question) ? implode(', ', $question) : $question;
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $p->fullname .' ('.$p->employee_id.')' }}</td>
                <td>{{ $p->business_unit }}</td>
                <td>{{ $p->location }}</td>
                <td>{{ $p->created_at->format('d M Y H:i') }}</td>
                <td>{{ $phone }}</td>
                <td>{{ $questionList ?: '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
