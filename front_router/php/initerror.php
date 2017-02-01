<h3><?php FrontRouter::i18n('PLUGIN_NAME'); ?></h3>
<p><?php FrontRouter::i18n('INIT_ERROR'); ?></p>
<ul>
  <?php foreach ($succ as $err) : ?>
  <li><?php echo $err; ?></li>
  <?php endforeach; ?>
</ul>