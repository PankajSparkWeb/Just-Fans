@php
$postInterest = isset($post) ? $post->interests->pluck('id')->toArray() : [];
@endphp  
<div class="checkboxes-outer">      
@foreach ($interests as $interest)
<div class='select-checkboxes'>
    <label class='cehckboxes-label'>              
        <input type="checkbox" class='input-checkboxes' name="interests[]" value="{{ $interest->id }}" {{ in_array($interest->id, $postInterest) ? 'checked' : '' }}>
        {{ $interest->name }}

    </label>
</div>
@endforeach
</div>
