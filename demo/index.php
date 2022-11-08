<?php
require_once "../dist/Translate.php";
$lang = filter_input(INPUT_GET, 'lang') ?? 'de';
Translate::setPrefix('signIn');
Translate::setLanguage($lang);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi"
          crossorigin="anonymous">
</head>
<body class="container py-5">

<div class="d-flex justify-content-center mb-5">
    <img src="table_translations.png" class="" alt="database picture">
</div>

<form action="" class="d-flex mb-3 justify-content-center">
    <div>
        <select class="form-select" name="lang" onchange="this.form.submit()">
            <option></option>
            <option>de</option>
            <option>en</option>
        </select>
    </div>
</form>


<h4><?= Translate::of('headline') ?></h4>
<form>
    <div class="mb-3">
        <label for="input_email"><?= Translate::of('email.label') ?>:</label>
        <input type="email" placeholder="<?= Translate::of('email.placeholder') ?>" id="input_email" class="form-control">
    </div>
    <div class="mb-3">
        <label for="input_password"><?= Translate::of('password.label') ?>:</label>
        <input type="password" placeholder="<?= Translate::of('password.placeholder') ?>" id="input_password" class="form-control">
    </div>

    <button type="button" class="btn btn-primary"><?= Translate::of('button.sign') ?></button>
</form>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3"
        crossorigin="anonymous"></script>
</body>
</html>



