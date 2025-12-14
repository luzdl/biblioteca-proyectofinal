<?php
$userForCard = $userForCard ?? [];
$nombre = (string)($userForCard['usuario'] ?? '');
$email = (string)($userForCard['email'] ?? '');
$rol = (string)($userForCard['rol'] ?? '');

$img = profile_image_url($userForCard);
?>
<section class="profile-box">
    <h2 class="subtitle">Información personal</h2>

    <p>
        <img src="<?php echo htmlspecialchars($img); ?>" alt="Usuario" style="width:72px;height:72px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-right:12px;">
        <b><?php echo htmlspecialchars($nombre); ?></b>
    </p>

    <p><b>Email:</b> <?php echo htmlspecialchars($email); ?></p>
    <p><b>Rol:</b> <?php echo htmlspecialchars($rol); ?></p>
</section>
