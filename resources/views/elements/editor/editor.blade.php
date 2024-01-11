<div class="toolbar">
  <button onclick="execCommand('bold')"><b>B</b></button>
  <button onclick="execCommand('italic')"><i>I</i></button>
  <button onclick="execCommand('underline')"><u>U</u></button>
  <select onchange="execCommand('formatBlock', this.value)">
    <option value="p">Paragraph</option>
    <option value="h1">Heading 1</option>
    <option value="h2">Heading 2</option>
    <option value="h3">Heading 3</option>
  </select>
</div>



<script>
  function execCommand(command, value = null) {
    document.execCommand(command, false, value);
  }
</script>