<?php use LogicLeap\PhpServerCore\TailwindUiRenderer; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <title><?php echo $variableData['site-title']; ?></title>
</head>

<body>
<?php TailwindUiRenderer::loadComponent('header-main', $variableData); ?>
</body>
</html>
