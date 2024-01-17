@foreach ($interests as $interest)
<label>              
    <input type="checkbox" name="interests[]" value="{{ $interest->id }}" >
    {{ $interest->name }}
</label><br>
@endforeach