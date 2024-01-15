<div id="mySelectBoxPost" class="d-flex justify-content-between">
    <div class="interest-post" value="option1">Option 1</div>
    <div class="interest-post" value="option2">Option 2</div>
    <div class="interest-post" value="option3">Option 3</div>
    <div class="interest-post" value="option4">Option 4</div>
    <div class="interest-post" value="option5">Option 5</div>
</div>
<script>var selectBox = document.getElementById('mySelectBoxPost');

    selectBox.addEventListener('click', function (event) {
        var div = event.target;
        if (div.tagName === 'DIV') {
            div.classList.toggle('selectedIntrest');
        }
    });</script>