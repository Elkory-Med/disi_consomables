<!DOCTYPE html>
<html>
<head>
    <title>SOMELEC-DISI - Compte approuvé</title>
</head>
<body>
    <h2>Bonjour {{ $user->name }},</h2>
    
    <p>Nous avons le plaisir de vous informer que votre compte sur la plateforme SOMELEC-DISI a été approuvé.</p>
    
    <p>Vous pouvez dès maintenant vous connecter et commencer à passer vos commandes sur notre plateforme.</p>
    
    <p>Pour accéder à votre compte, veuillez cliquer sur le lien suivant : <a href="{{ url('/auth/login') }}">Se connecter</a></p>
    
    <p>Cordialement,<br>
    L'équipe SOMELEC-DISI</p>
</body>
</html>
