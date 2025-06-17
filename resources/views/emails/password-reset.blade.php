<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Réinitialisation de mot de passe</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
        <h2 style="color: #dc3545;">Réinitialisation de mot de passe</h2>
        
        <p>Bonjour {{ $user->full_name }},</p>
        
        <p>Vous avez demandé la réinitialisation de votre mot de passe. Voici votre code de vérification :</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <span style="font-size: 32px; font-weight: bold; background: #f8f9fa; padding: 15px 30px; border: 2px solid #dc3545; border-radius: 8px; letter-spacing: 5px;">{{ $code }}</span>
        </div>
        
        <p><strong>⏰ Ce code expire à {{ $expiresAt }}</strong></p>
        
        <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        <p style="font-size: 12px; color: #666;">
            Cet email a été envoyé automatiquement, merci de ne pas y répondre.
        </p>
    </div>
</body>
</html>