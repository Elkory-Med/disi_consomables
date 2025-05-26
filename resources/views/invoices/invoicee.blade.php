<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Facture - Commande #{{ $order->id }}</title>
  <link rel="preload" href="http://127.0.0.1:8000/build/assets/app-Cer738zS.css" as="font" crossorigin="">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
  <style>
    body {
      background: #EEE;
      font-family: 'Montserrat', sans-serif;
      margin: 0;
      padding: 0;
    }
    .invoice {
      background: #fff;
      width: 100%; /* Adjusted to be responsive */
      max-width: 970px; /* Ensures it doesn't exceed this width */
      margin: 50px auto;
      border-radius: 10px;
      padding: 25px;
      box-shadow: none; /* Removed shadow effect */
      overflow: hidden; /* Prevents shadow overflow */
    }
    .invoice-header {
      padding: 25px 0;
      border-bottom: 1px solid #ddd;
    }
    .invoice-footer {
      padding: 15px;
      font-size: 0.9em;
      text-align: center;
      color: #999;
      border-top: 1px solid #ddd;
      margin-top: 30px; /* Slightly increased margin for a little space */
    }
    .logo {
      max-height: 100px; /* Increased logo size */
      border-radius: 10px;
    }
    .dl-horizontal {
      margin: 0;
    }
    .dl-horizontal dt {
      float: left;
      width: 80px;
      overflow: hidden;
      clear: left;
      text-align: right;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .dl-horizontal dd {
      margin-left: 90px;
    }
    h1, h4 {
      margin: 0;
    }
    .text-muted {
      color: #6c757d;
    }
    .spacing {
      margin-bottom: 20px; /* Space between groups */
    }
    .example-class {
      margin: 10px;
    }
  </style>
</head>
<body>

<div class="container invoice">
  <div class="invoice-header spacing">
    <div class="row">
      <div class="col-xs-8 text-right">
        <h5 class="text-muted">Commande #{{ $order->id }}</h5>
        <h5 class="text-muted">Date: {{ date('d/m/Y') }}</h5>
      </div>
      <div class="col-xs-4">
        <div class="media">
          <div class="media-left" style="margin-right: 15px;">
            <img class="media-object logo" src="{{ $logoPath }}" alt="Logo" style="max-width: 100px;" />
          </div>
          <div class="media-body text-right" style="margin-top: 10px;">
            <span style="font-size: 0.7em;">RECEPTION INTERNE</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="invoice-body spacing">
    <div class="row">
      <div class="col-xs-5">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Détails de la commande</h3>
          </div>
          <div class="panel-body">
            <div class="order-details">
              <h4>Détails de la commande</h4>
              <ul class="list-unstyled">
                <li>ID de commande: {{ $order->id }}</li>
                <li>Utilisateur: {{ $order->user->name }}</li>
                <li>Statut: {{ $order->status === 'approved' ? 'approuvé' : $order->status }}</li>
                <li>Créé le: {{ $order->created_at }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xs-7">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Détails de l'utilisateur</h3>
          </div>
          <div class="panel-body user-details">
            <div class="user-details">
              <h4>Détails de l'utilisateur</h4>
              <ul class="list-unstyled">
                <li>Administration: {{ $order->user->administration }}</li>
                <li>Unité: {{ $order->user->unite }}</li>
                <li>Matricule: {{ $order->user->matricule }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="panel panel-default spacing">
      <div class="panel-heading">
        <h3 class="panel-title">Articles de la commande</h3>
      </div>
      <table class="table table-bordered table-condensed">
      <thead>
        <tr>
          <th scope="col" style="padding-right: 30px;">Nom du produit</th>
          <th scope="col" class="text-center colfix" style="padding-right: 30px;">Quantité</th>
          <th scope="col" class="text-center colfix">N° de série</th>
        </tr>
      </thead>
        <tbody>
          @foreach($order->order_items as $item)
          <tr>
            <td>{{ $item->product->name }}</td>
            <td class="text-center">{{ $item->quantity }}</td>
            <td class="text-center"> <!-- Removed serial number input field --> </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  
  <div class="invoice-footer">
    <strong>~SOMELEC-DISI COMMANDES~</strong>
  </div>
</div>

</body>
</html>