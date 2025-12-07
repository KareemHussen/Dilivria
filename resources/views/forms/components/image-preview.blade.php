@php
    // $getState() is a closure that returns the actual field value
    $picture = $getState();
@endphp

@if ($picture)
    <img 
        src="{{ asset('storage/' . $picture) }}" 
        style="max-height: 150px; border-radius: 8px;"
    >
@else
    <p>No picture available.</p>
@endif