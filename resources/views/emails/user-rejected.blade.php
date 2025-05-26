<!DOCTYPE html>
<html>
<head>
    <title>SOMELEC-DISI - Rejet de votre demande d'inscription</title>
</head>
<body>
    <h2>Bonjour {{ $user->name }},</h2>
    
    <p>Nous regrettons de vous informer que votre demande d'inscription sur la plateforme SOMELEC-DISI n'a pas été approuvée.</p>
    
    @if($reason)
    <p><strong>Motif du rejet :</strong> {{ $reason }}</p>
    @endif
    
    <p>Pour toute information complémentaire, veuillez contacter l'administration SOMELEC-DISI.</p>
    
    <p>Cordialement,<br>
    L'équipe SOMELEC-DISI</p>
</body>
</html>
