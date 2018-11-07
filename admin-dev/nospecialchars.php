<?php
// No Special Chars!
?>
    <script>
        $(document).ready(function () {
            $("input[type=text]").keydown(function(e) {if ($.inArray(e.keyCode, [8]) !== -1 || (e.keyCode >= 35 && e.keyCode <= 39)) { return true; } return /\s*[a-zA-Z0-9,\s]+\s*/.test(String.fromCharCode(e.keyCode));});
        });
    </script>
<?php
// End
?>
