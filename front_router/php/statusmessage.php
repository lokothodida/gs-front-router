<?php namespace FrontRouterPlugin; ?>
<script>
  $(document).ready(function() {
    $('div.bodycontent').before('<div class="' + <?php echo json_encode($status); ?> + '" style="display:block;">'+<?php echo json_encode($msg); ?>+'</div>');
  }); // ready
</script>