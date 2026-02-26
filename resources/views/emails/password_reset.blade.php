<!DOCTYPE html>
<html>

<head>
    <title>Recuperar Contraseña</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div
        style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h2 style="color: #16a34a; text-align: center;">AgroMarket 🚜</h2>
        <p>Hola,</p>
        <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta asociada al correo
            <strong>{{ $email }}</strong>.</p>
        <p>Si fuiste tú, haz clic en el siguiente botón para crear una nueva contraseña:</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}"
                style="background-color: #16a34a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Restablecer
                Contraseña</a>
        </div>

        <p>Este enlace expirará en 60 minutos.</p>
        <p style="font-size: 12px; color: #999;">Si no solicitaste esto, puedes ignorar este correo de forma segura.</p>
    </div>
</body>

</html>