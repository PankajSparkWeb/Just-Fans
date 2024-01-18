@php
$postInterest = isset($post) ? $post->interests->pluck('id')->toArray() : [];
@endphp        
@foreach ($interests as $interest)
    <label>              
        <input type="checkbox" name="interests[]" value="{{ $interest->id }}" {{ in_array($interest->id, $postInterest) ? 'checked' : '' }}>
        {{ $interest->name }}

    </label><br>
@endforeach
