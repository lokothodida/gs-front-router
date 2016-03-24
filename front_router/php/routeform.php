<?php namespace FrontRouterPlugin; ?>

<div class="route">
  <a href="#" class="deleteroute">&times;</a>
  <p>
    <label for="route[]"><?php i18n('ROUTE'); ?>:</label>
    <input class="text" name="route[]" value="<?php echo $route; ?>" required/>
  <p>
  <p>
    <label for="route[]"><?php i18n('ACTION'); ?>:</label>
    <textarea class="text" name="callback[]"><?php echo $callback; ?></textarea>
  </p>
</div>