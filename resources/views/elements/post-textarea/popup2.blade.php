@if(session('openPopup'))
<script>
    // Open the popup using JavaScript, you can use a library like Bootstrap modal or any other modal library
    // Here's an example using Bootstrap modal
    $(document).ready(function() {
        $('#popupModal').modal('show');
    });
</script>
@endif

<!-- Your popup modal HTML -->
<div class="modal fade" id="popupModal" tabindex="-1" role="dialog" aria-labelledby="popupModalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="popupModalLabel">Popup Form</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
           testing popup
        </div>
    </div>
</div>
</div>
@endsection