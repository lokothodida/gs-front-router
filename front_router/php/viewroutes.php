<?php namespace FrontRouterPlugin; ?>

<!--css-->
<link rel="stylesheet" href="<?php echo $css['codemirror']; ?>">
<style>
  .routeform textarea, .CodeMirror,
  .CodeMirror-gutter, .CodeMirror-scroll { height: 350px !important; }

  .CodeMirror {
    background: #fff;
  }

  .routeform .route {
    border: 1px solid #aaa;
    padding: 10px;
    border-radius: 5px;
    margin: 0 0 10px 0;
    background: #f9f9f9;
  }

  .routeform .route .deleteroute {
    float: right;
  }
</style>

<h3 class="floated"><?php i18n('MANAGE_ROUTES'); ?></h3>
<nav class="edit-nav clearfix">
  <a href="#" class="addroute"><?php i18n('ADD_ROUTE'); ?></a>
</nav>

<form method="post" class="routeform">
  <div class="routes">
    <?php foreach ($routes as $route => $callback) : ?>
    <?php include('routeform.php'); ?>
    <?php endforeach; ?>
  </div>

  <p id="submit_line">
    <input type="submit" class="submit" name="save" value="<?php \i18n('BTN_SAVECHANGES'); ?>">
  </p>
</form>

<!--javascript-->
<script type="text/template" class="routetemplate">
  <?php
    $route = '';
    $callback = '';
    include('routeform.php');
  ?>
</script>
<script src="<?php echo $js['codemirror']; ?>"></script>
<script type="text/javascript">
  $(function() {
    // Make a Codemirror Instance
    var makeEditor = function(textarea) {
      var editor = CodeMirror.fromTextArea(textarea, {
        lineNumbers: true,
      });
    };

    $('.routeform .route textarea').each(function() {
      makeEditor(this);
    });

    // Make routes sortable
    $('.routeform .routes').sortable({
      cancel: 'textarea, .CodeMirror, .deleteroute, input',
    });

    // Add route
    $('body').on('click', '.addroute', function() {
      var $template = $($('.routetemplate').html());
      $('.routes').append($template);

      // Enable CodeMirror on the new textarea
      makeEditor($template.find('textarea')[0]);
      $template[0].scrollIntoView();

      return false;
    });

    // Delete route
    $('.routeform').on('click', '.deleteroute', function(evt) {
      var $route = $(evt.target).closest('.route');
      var route = $route.find('input').val();
      var status = confirm(<?php echo json_encode(i18n_r('DELETE_ROUTE_SURE')); ?>.replace('%route%', route));

      if (status) {
        $route.remove();
      }

      return false;
    });
  });
</script>