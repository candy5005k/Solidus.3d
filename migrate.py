import sys

with open('d:\\Downloads\\kgroupads.in\\websites\\Solidus.3d\\index.html', 'r', encoding='utf-8') as f:
    text = f.read()

# Extract CSS
start_style = text.find('<style>')
end_style = text.find('</style>') + 8
css_content = text[start_style + 7 : end_style - 8]

# Save to main.css
with open('d:\\Downloads\\kgroupads.in\\websites\\Solidus.3d\\assets\\css\\main.css', 'w', encoding='utf-8') as f:
    f.write(css_content)

# Extract Nav (from <nav id="nav" to </nav>)
start_nav = text.find('<nav id="nav"')
end_nav = text.find('</nav>') + 6
nav_content = text[start_nav : end_nav]

# Extract mobile drawer
start_mob = text.find('<div class="mob" id="mob">')
end_mob = text.find('</div>\n\n  <!-- NAV -->') + 6
mob_content = text[start_mob : end_mob]

# Extract footer
start_footer = text.find('<footer>')
end_footer = text.find('</footer>') + 9
footer_content = text[start_footer : end_footer]

# Make header.php and footer.php
header_php = '''<?php
// includes/header.php
require_once __DIR__ . '/config.php';
$cssPath = __DIR__ . '/../assets/css/main.css';
$jsPath  = __DIR__ . '/../assets/js/main.js';
$cssVer  = file_exists($cssPath) ? filemtime($cssPath) : time();
$jsVer   = file_exists($jsPath)  ? filemtime($jsPath)  : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Solidus 3D Modeling & CAD Design Services'; ?></title>
  <meta name="description" content="<?= isset($pageDesc) ? htmlspecialchars($pageDesc) : 'Professional 3D modeling, CAD design, and reality capture services.'; ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Mono:wght@400;500&family=IBM+Plex+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= h(asset_url('css/main.css')); ?>?v=<?= $cssVer; ?>">
  <script defer src="<?= h(asset_url('js/main.js')); ?>?v=<?= $jsVer; ?>"></script>
</head>
<body>
''' + mob_content + '\n' + nav_content

footer_php = footer_content + '''
</body>
</html>
'''

with open('d:\\Downloads\\kgroupads.in\\websites\\Solidus.3d\\includes\\header.php', 'w', encoding='utf-8') as f:
    f.write(header_php)

with open('d:\\Downloads\\kgroupads.in\\websites\\Solidus.3d\\includes\\footer.php', 'w', encoding='utf-8') as f:
    f.write(footer_php)

# Create index.php
start_head = text.find('<head>')
index_body_start = end_nav
index_body_end = start_footer
index_php_content = '<?php\n$pageTitle = "Solidus 3D Modeling - CAD Design Services";\nrequire_once __DIR__ . "/includes/header.php";\n?>\n'
index_php_content += text[index_body_start : index_body_end]
index_php_content += '<?php\nrequire_once __DIR__ . "/includes/footer.php";\n?>'

with open('d:\\Downloads\\kgroupads.in\\websites\\Solidus.3d\\index.php', 'w', encoding='utf-8') as f:
    f.write(index_php_content)

print('Migration successful')
