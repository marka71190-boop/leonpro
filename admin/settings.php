<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
require __DIR__ . '/_layout.php';
require_admin();

$schema = settings_schema();

if (is_post()) {
    csrf_check();
    try {
        foreach ($schema as $key => [$group, $label, $type]) {
            switch ($type) {
                case 'image':
                    if ($new = handle_upload($key, ['jpg', 'png', 'webp', 'svg'], 'site')) {
                        delete_upload(setting($key));
                        setting_save($key, $new);
                    } elseif (post($key . '_remove') === '1') {
                        delete_upload(setting($key));
                        setting_save($key, '');
                    }
                    break;
                case 'bool':
                    setting_save($key, post($key) === '1' ? '1' : '0');
                    break;
                case 'int':
                    setting_save($key, (string)max(0, (int)post($key)));
                    break;
                case 'color':
                    $c = post($key);
                    setting_save($key, preg_match('~^#[0-9a-f]{6}$~i', $c) ? $c : '#F07F1F');
                    break;
                case 'code':
                    setting_save($key, trim((string)($_POST[$key] ?? '')));
                    break;
                default:
                    setting_save($key, post($key));
            }
        }
        flash('Настройки сохранены.');
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'error');
    }
    redirect('admin/settings.php');
}

$groups = [];
foreach ($schema as $key => $def) {
    $groups[$def[0]][$key] = $def;
}

admin_header('Настройки сайта', 'settings');
?>
<h1>Настройки сайта</h1>
<div class="a-filters">
  <?php foreach (array_keys($groups) as $g): ?><a class="a-chip" href="#g-<?= e($g) ?>"><?= e($g) ?></a><?php endforeach; ?>
</div>
<form method="post" enctype="multipart/form-data" class="a-form">
  <?= csrf_field() ?>
  <?php foreach ($groups as $g => $fields): ?>
    <div class="a-card" id="g-<?= e($g) ?>">
      <h2><?= e($g) ?></h2>
      <div class="a-grid">
      <?php foreach ($fields as $key => [, $label, $type, , $hint]):
          $val = setting($key);
          switch ($type) {
              case 'textarea': echo '<div class="a-field-wide">' . a_textarea($key, $label, $val, $hint, false, 3) . '</div>'; break;
              case 'code': echo '<label class="a-field a-field-wide"><span>' . e($label) . '</span><textarea class="mono" name="' . e($key) . '" rows="4">' . e($val) . '</textarea><small>' . e($hint) . '</small></label>'; break;
              case 'image': echo a_image($key, $label, $val, $hint); break;
              case 'bool': echo '<div class="a-field"><span>' . e($label) . '</span>' . a_check($key, 'Включено', $val === '1') . '<small>' . e($hint) . '</small></div>'; break;
              case 'int': echo a_input($key, $label, $val, 'number', $hint, ['min' => 0]); break;
              case 'color': echo a_input($key, $label, $val, 'color', $hint); break;
              default: echo a_input($key, $label, $val, 'text', $hint);
          }
      endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="a-save"><button class="a-btn" type="submit">Сохранить настройки</button></div>
</form>
<?php admin_footer();
