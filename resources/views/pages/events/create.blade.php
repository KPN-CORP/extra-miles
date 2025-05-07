@extends('layouts_.vertical', ['page_title' => 'Events'])

@section('content')
<div class="container">
    <h1>Create Event</h1>

    <form method="POST" action="#">
        @csrf
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <input type="text" class="form-control" name="category" id="category">
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Event Title</label>
            <input type="text" class="form-control" name="title" id="title">
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-control">
                <option value="Archive">Archive</option>
                <option value="Open Registration">Open Registration</option>
                <option value="Full Booked">Full Booked</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save Event</button>
    </form>
</div>
@endsection
