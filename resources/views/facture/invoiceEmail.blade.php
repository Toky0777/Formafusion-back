<!DOCTYPE html>
<html>

<head>
    <title>Facture</title>
</head>

<body>
    <h1>Bonjour {{ $entreprise->etp_name }},</h1>
    <p>Veuillez trouver ci-joint la facture numero : {{ $invoice->invoice_number }}.</p>
    <p>Merci de nous avoir choisis.</p>
    <p>Cordialement,</p>
    <p>L'équipe {{ $customer->customerName }}</p>
</body>

</html>
