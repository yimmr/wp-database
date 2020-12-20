<div class="im-error-main">
    <div class="exc-message">
        <p class="exc-class"><?php echo $inspector->getExceptionName() . '(' . $code . ')'; ?></p>
        <p class="exc-msg"><?php echo $message; ?></p>
        <p class="ui-url"><?php echo $inspector->getException()->getFile(); ?></p>
    </div>

    <div class="tabs">
        <div class="tab-nav">
            <ul>
                <li class="active" data-tab="1">Stack trace</li>
                <li data-tab="2">Data</li>
                <li data-tab="3">Cookie</li>
                <li data-tab="4">Session</li>
                <li data-tab="5">Server</li>
            </ul>
        </div>

        <div class="tab-main">
            <div data-tab="1" style="display: block;">
                <?php include $this->getResource('view/frame-code.php');?>
            </div>
            <div data-tab="2">
                <h3>GET</h3>
                <dl class="definition-list" style="margin-bottom: 2rem;">
                    <?php foreach ($tables['GET Data'] as $key => $value) {?>
                    <?php printf('<dt>%s</dt><dd>%s</dd>', $key, $value);?>
                    <?php }?>
                </dl>
                <h3>POST</h3>
                <dl class="definition-list" style="margin-bottom: 2rem;">
                    <?php foreach ($tables['POST Data'] as $key => $value) {?>
                    <?php printf('<dt>%s</dt><dd>%s</dd>', $key, $value);?>
                    <?php }?>
                </dl>
                <h3>Files</h3>
                <dl class="definition-list">
                    <?php foreach ($tables['Files'] as $key => $value) {?>
                    <?php printf('<dt>%s</dt><dd>%s</dd>', $key, $value);?>
                    <?php }?>
                </dl>
            </div>
            <div data-tab="3">
                <dl class="definition-list">
                    <?php foreach ($tables['Cookies'] as $key => $value) {?>
                    <?php printf('<dt>%s</dt><dd>%s</dd>', $key, $value);?>
                    <?php }?>
                </dl>
            </div>
            <div data-tab="4">
                <dl class="definition-list">
                    <?php foreach ($tables['Session'] as $key => $value) {?>
                    <?php printf('<dt>%s</dt><dd>%s</dd>', $key, $value);?>
                    <?php }?>
                </dl>
            </div>
            <div data-tab="5">
                <dl class="definition-list">
                    <?php foreach ($tables['Server/Request Data'] as $key => $value) {?>
                    <?php printf('<dt>%s</dt><dd>%s</dd>', $key, $value);?>
                    <?php }?>
                </dl>
            </div>
        </div>
    </div>
    <script>
    imExceptionPageTabs('.im-error-main > .tabs');
    </script>
</div>