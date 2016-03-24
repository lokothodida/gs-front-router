<?php namespace FrontRouterPlugin; ?>

<h3><?php i18n('PLUGIN_NAME'); ?></h3>
<p><?php i18n('INIT_ERROR'); ?></p>
<ul>
  <?php foreach ($succ as $err) : ?>
  <li><?php i18n($err); ?></li>
  <?php endforeach; ?>
</ul>