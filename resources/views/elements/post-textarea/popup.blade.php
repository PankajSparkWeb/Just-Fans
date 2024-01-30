<div class="interest-on-signup">
    <!-- Add style="display:none;" to hide the dialog initially -->
    <div id="dialog-container" class="dialog-container" style="display:block;">
        <div class="dialog-box">
            <span class="close-btn" onclick="closeDialog()">Skip</span>          
            <form method="POST" action="{{route('my.settings.profile.saveInterest')}}">              
                @csrf
                <div class="checkbox-container justify-content-between">

                    @php
                    $userInterests = Auth::user()->interests->pluck('id')->toArray();
                    @endphp

                    @foreach ($interests as $interest)
                        <label>              
                            <input type="checkbox" name="interests[]" value="{{ $interest->id }}" {{ in_array($interest->id, $userInterests) ? 'checked' : '' }}>
                            {{ $interest->name }}
                        </label><br>
                    @endforeach
                    <!-- Add other checkbox elements as needed -->

                </div>

                <input type="submit" value="Submit">
            </form>
        </div>
    </div>
</div>
