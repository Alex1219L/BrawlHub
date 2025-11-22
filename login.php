<?php
session_start();
include('conexion.php'); 

$mensaje = "";
$color = "var(--text)";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $sql = "SELECT id, contrasena FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hash);
        $stmt->fetch();

        if (password_verify($contrasena, $hash)) {
            $_SESSION['usuario'] = $usuario;
            $mensaje = "✅ Inicio de sesión exitoso. Redirigiendo...";
            $color = "green";
            header("refresh:2;url=index.html"); 
        } else {
            $mensaje = "❌ Contraseña incorrecta.";
            $color = "red";
        }
    } else {
        $mensaje = "⚠️ Usuario no encontrado.";
        $color = "red";
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $nuevoUsuario = $_POST['nuevo_usuario'];
    $nuevaContrasena = password_hash($_POST['nueva_contrasena'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (usuario, contrasena) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nuevoUsuario, $nuevaContrasena);

    if ($stmt->execute()) {
        $_SESSION['registro_exitoso'] = true; 
        header("Location: login.php"); 
        exit();
    } else {
        $mensaje = "❌ Error al registrar: " . $conn->error;
        $color = "red";
        
        $_POST['showRegister'] = 'true'; 
    }

    $stmt->close();
}

if (isset($_SESSION['registro_exitoso'])) {
    $mensaje = "✅ Usuario creado correctamente. ¡Ahora puedes iniciar sesión!";
    $color = "green";
    unset($_SESSION['registro_exitoso']);
}


$conn->close();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BrawlHub — Login</title>
  <link rel="stylesheet" href="css/style.css">
  </head>
<body>
  <div class="login-wrap">
    <div class="decor-top-right" aria-hidden="true"></div>
    <div class="brand-bar">
      <div class="logo" aria-hidden="false">
        <img src="Brawl_Stars_logo_2025.svg.png" alt="BrawlHub logo" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="logo-fallback" style="display:none">BH</div>
      </div>
      <div class="title-group">
        <h1>BrawlHub</h1>
        <p>Inicia sesión o crea una cuenta</p>
      </div>
    </div>

    <div class="card">
      <form method="POST" style="display:<?php echo isset($_POST['showRegister']) ? 'none':'block'; ?>;">
        <div class="form-group">
          <label for="usuario">Usuario</label>
          <input type="text" id="usuario" name="usuario" 
                 value="<?php echo isset($_POST['usuario']) && empty($mensaje) ? htmlspecialchars($_POST['usuario']) : ''; ?>">
        </div>

        <div class="form-group">
          <label for="contrasena">Contraseña</label>
          <input type="password" id="contrasena" name="contrasena" >
        </div>

        <div class="actions">
          <button class="btn btn-primary" type="submit" name="login">Iniciar Sesión</button>
          <button class="btn btn-secondary" type="submit" name="showRegister">Crear nuevo usuario</button>
        </div>
      </form>

      <form method="POST" style="display:<?php echo isset($_POST['showRegister']) ? 'block':'none'; ?>;">
        <div class="form-group">
          <label for="nuevo_usuario">Nuevo Usuario</label>
          <input type="text" id="nuevo_usuario" name="nuevo_usuario" required 
                 value="<?php echo isset($_POST['nuevo_usuario']) && $color == 'red' ? htmlspecialchars($_POST['nuevo_usuario']) : ''; ?>">
        </div>

        <div class="form-group">
          <label for="nueva_contrasena">Nueva Contraseña</label>
          <input type="password" id="nueva_contrasena" name="nueva_contrasena" required>
        </div>

        <div class="actions">
          <button class="btn btn-primary" type="submit" name="register">Registrar Usuario</button>
          <button class="btn btn-secondary" type="button" onclick="window.location.href='login.php'">← Volver al Login</button>
        </div>
      </form>
    </div>

    <?php if (!empty($mensaje)): ?>
      <div class="message" style="color: <?php echo $color; ?>;">
        <?php echo $mensaje; ?>
      </div>
    <?php endif; ?>

    <div class="small"><a href="index.html" class="link-return">← Volver al Inicio</a></div>
  </div>
</body>
</html>