<?php

// 
// Template for rendering example content.
// 

use UUP\Web\Component\Script\CodeBox;

?>

<style>
    .section {
        display: none;
    }
</style>

<script>
        function toggle_display(id)
        {
            const elem = document.getElementById(id);

            if (id === 'output') {
                document.getElementById('source').style.display = '';
            }
            if (id === 'source') {
                document.getElementById('output').style.display = '';
            }

            if (elem.style.display === 'block') {
                elem.style.display = '';
            } else {
                elem.style.display = 'block';
            }
        }
</script>

<h2>Example <?= $filename ?></h2>

<div class="w3-padding-12">
    <a class="w3-btn section-button" onclick="toggle_display('output')">Output</a>
    <a class="w3-btn section-button" onclick="toggle_display('source')">Source</a>

    <div id="output" class="section">
        <pre class="w3-code"><?php require($filename) ?></pre>
    </div>

    <div id="source" class="section">
        <?php CodeBox::outputFile($filename, true) ?>
    </div>
</div>

<div class="w3-panel w3-green">
    <p>
        Click on output to see result from running script. Use source to inspect the 
        PHP code for example <?= $filename ?>.
    </p>
</div>
<?php if ($callback) : ?>
        <?php $callback() ?>
<?php endif; ?>
