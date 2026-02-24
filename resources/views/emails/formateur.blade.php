<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Invitation</title>
</head>

<body>
  <h3>INVITATION de la part de {{ $data->customerName }}</h3>
  <p>Veuillez vous connectez avec les identifiant suivants :</p>
  <p>Identifiant : <span style="font-weight: 700">{{ $mail }}</span></p>
  <p>Mot de passe : <span style="font-weight: 700">{{ $password }}</span></p>
  <p>en suivant le lien suivant
    <a href="https://mg.formafusion.io/login">lgcfp.com</a>
  </p>
</body>

</html>
