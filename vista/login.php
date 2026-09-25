<?php
session_name("LOGIN");
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Dulce Micro</title>
    <link rel="icon" type="image/png" href="/Dulce_Micro/img_logos/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        :root {
            --dm-rosa: #f7a8c4;
            --dm-morado: #c9a0dc;
            --dm-rosa-fuerte: #e685a8;
            --dm-morado-fuerte: #a879c9;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--dm-rosa) 0%, var(--dm-morado) 100%);
        }
        .login-card {
            border: none;
            border-radius: 1.25rem;
            overflow: hidden;
        }
        .login-card .card-header {
            background: #fff;
            text-align: center;
            padding: 2rem 1.5rem 1rem;
            border: none;
        }
        .login-card .card-header img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 0 0 4px var(--dm-rosa);
            margin-bottom: .75rem;
        }
        .login-card .card-header h3 {
            color: var(--dm-morado-fuerte);
            font-weight: 700;
            margin-bottom: 0;
        }
        .login-card .card-body { padding: 1.5rem 2rem 2rem; }
        .form-label { color: #6b4e6e; font-weight: 600; font-size: .9rem; }
        .form-control:focus {
            border-color: var(--dm-morado);
            box-shadow: 0 0 0 .2rem rgba(201,160,220,.25);
        }
        .btn-dulce {
            background: linear-gradient(90deg, var(--dm-rosa-fuerte), var(--dm-morado-fuerte));
            border: none;
            color: #fff;
            font-weight: 600;
            padding: .6rem;
        }
        .btn-dulce:hover {
            background: linear-gradient(90deg, var(--dm-morado-fuerte), var(--dm-rosa-fuerte));
            color: #fff;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="card login-card shadow" style="width: 100%; max-width: 400px;">
            <div class="card-header">
                <img src="/Dulce_Micro/img_logos/logo principal.png"
                     alt="Logotipo de Dulce Micro: una porción de pastel con glaseado morado, chispas de colores y un corazón rosado encima, sobre un plato blanco, con el texto Dulce Micro en letras rosadas y moradas dentro de un círculo rosa.">
                <h3>!BIENVENIDO DE NUEVO!<br><br>Dulce Micro</h3>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">Correo o contraseña incorrectos.</div>
                <?php endif; ?>
                <?php if (isset($_GET['expirada'])): ?>
                    <div class="alert alert-warning text-center">
    Tu sesión expiró por inactividad.<br>
    Ingresa de nuevo.
</div>
                <?php endif; ?>

                <form action="../controlador/LoginController.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="clave" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-dulce w-100">Ingresar</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>