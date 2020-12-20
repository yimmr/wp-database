<?php
$count = count($frames);
foreach ($frames as $i => $frame) {
    $line       = $frame->getLine();
    $filePath   = $frame->getFile();
    $editorHref = $filePath ? $this->getEditorHref($filePath, (int) $line) : '';
    $editorHref = $editorHref ?: 'javascript:;';?>
<div class="stack-trace-item">
    <label for="<?php echo 'tab' . $i . $line; ?>">
        <div class="header">
            <span class="number"><?php echo max($count -= 1, 0) ?></span>
            <div>
                <?php printf('<a href="%s">%s: <span>%s</span></a>', $editorHref, $filePath, $line);?>

                <span class="sub frame-class"><?php echo $frame->getClass(); ?></span>
                <span class="sub frame-function"><?php echo $this->escape($frame->getFunction() ?: '') ?></span>
            </div>
        </div>
    </label>
    <input id="<?php echo 'tab' . $i . $line; ?>" type="radio" name="code<?php echo $page_num; ?>"
        <?php echo $i == 0 ? 'checked' : '' ?> style="display: none;" />

    <?php if ($line && ($range = $frame->getFileLines($line - 20, 40))) {?>

    <?php $range = array_map(function ($line) {return empty($line) ? ' ' : $line;}, $range);?>
    <?php $start = key($range) + 1;?>
    <div class="code-block">
        <div class="lines">
            <?php foreach ($range as $num => $value) {?>
            <?php printf('<p %s>%s</p>', (($num += 1) == $line ? 'class="highlight"' : ''), $num);?>
            <?php }?>
        </div>
        <pre>
            <?php foreach ($range as $num => $value) {?>
            <?php printf('<p %s>%s</p>', (($num += 1) == $line ? 'class="highlight"' : ''), $this->escape($value));?>
            <?php }?>
        </pre>
    </div>

    <?php }?>
</div>
<?php }?>