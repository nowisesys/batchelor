<?php
$test = $this->params->getParam('test', 'func');

switch ($test) {
        case 'func':
                $extra = [
                        'info' => "Switch to run performance test on this cache backend instead:",
                        'hint' => false,
                        'href' => "?test=time",
                        'text' => "Performance"
                ];
                break;
        case 'time':
                $extra = [
                        'info' => "Switch to run ordinary function test on this cache backend instead:",
                        'hint' => "The number of ierations can be changed from by appending 'limit=xxx' on URL.",
                        'href' => "?test=func",
                        'text' => "Functions"
                ];
                break;
}

?>

<div class="w3-panel w3-padding w3-light-gray">
    <h4>Select test suite</h4>
    <p>Cache examples supports multiple tests. <?= $extra['info'] ?></p>
    <a class="w3-btn w3-green" href="<?= $extra['href'] ?>"><?= $extra['text'] ?></a>
</div>
<?php if ($extra['hint']) : ?>
        <div class="w3-panel">
            <h4>More options</h4>
            <p><?= $extra['hint'] ?></p>
        </div>
<?php endif; ?>
