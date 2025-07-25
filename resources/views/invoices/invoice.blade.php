<!DOCTYPE html>
<html>
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
      width: 100%;
      min-height: 100vh;
      padding: 20px;
      margin: 0;
      display: flex;
      flex-direction: column;
    }
    .invoice-header {
      width: 100%;
      border-bottom: 2px solid #ddd;
      margin-bottom: 30px;
      padding-bottom: 20px;
    }
    .invoice-footer {
      font-size: 0.9em;
      text-align: center;
      color: #999;
      border-top: 1px solid #ddd;
      margin-top: -1;
      padding: 1px;
    }
    .logo {
      max-height: 100px;
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
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<div class="invoice">
  <!-- Single Line Header -->
  <table style="width: 100%; margin: 20px 0 0;">
    <tr>
      <td style="width: 100px; padding: 0 20px;">
        @if(isset($logoPath) && file_exists($logoPath))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Logo" style="width: 77px; height: 75px; object-fit: contain;" />
        @else
            <div style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; background: #f5f5f5; border-radius: 10px;">
                <span style="color: #666;">LOGO</span>
            </div>
        @endif
      </td>
      <td style="text-align: center;">
      </td>
      <td style="width: 100px; text-align: left; padding: 0 20px;">
        <span style="color: #666; font-size: 12px;">Date: {{ date('d/m/Y') }}</span><br>
        <span style="color: #666; font-size: 12px;">Commande: #{{ $order->id }}</span>
      </td>
    </tr>
  </table>

  <!-- Main Title -->
  <div class="text-center" style="margin-top: 15px; border-bottom: 2px solid #ddd; padding-bottom: 10px;">
    <h2 style="font-size: 16px; margin: 0; color: #333; font-weight: bold; text-align: center;">RECEPTION INTERNE</h2>
  </div>
  
  <div class="invoice-body" style="margin-top: 20px;">
    <!-- Details Section -->
    <div style="margin-bottom: 10px;">
      <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 5px;">
        <h3 style="color: #333; margin-bottom: 15px; font-size: 18px;">Détails de la commande</h3>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <div>ID de commande: {{ $order->id }}</div>
          <div>Utilisateur: {{ $order->user->name }}</div>
          <div>Statut: {{ $order->status === 'approved' ? 'approuvé' : $order->status }}</div>
          <div>Créé le: {{ $order->created_at->locale('fr')->translatedFormat('d F Y à H:i:s') }}</div>
        </div>
      </div>
      
      <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 5px;">
        <h3 style="color: #333; margin-bottom: 15px; font-size: 18px;">Détails de l'utilisateur</h3>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <div>Direction: {{ $order->user->administration }}</div>
          <div>Unité: {{ $order->user->unite }}</div>
          <div>Matricule: {{ $order->user->matricule }}</div>
        </div>
      </div>

      <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 5px;">
        <h3 style="color: #333; margin-bottom: 15px; font-size: 18px;">Articles de la commande</h3>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          @foreach($order->order_items as $item)
          <div>Nom du produit: {{ $item->product->name }}</div>
          <div>Quantité: {{ $item->quantity }}</div>
          @endforeach
        </div>
      </div>

      <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 5px;">
        <h3 style="color: #333; margin-bottom: 15px; font-size: 18px;">Numéros de série</h3>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          @if(isset($serialNumbers))
            @foreach($order->order_items as $item)
              <div style="margin-bottom: 10px;">
                <h4 style="color:rgb(39, 42, 44); margin-bottom: 10px;">{{ $item->product->name }}</h4>
                @if(isset($serialNumbers[$item->id]))
                  @foreach($serialNumbers[$item->id] as $index => $serial)
                    <p style="margin: 5px 0; font-size: 11px; font-weight: bold;">Numéro {{ $index + 1 }}: {{ $serial }}</p>
                  @endforeach
                @endif
              </div>
              @if(!$loop->last)
                <hr style="margin: 10px 0; border-top: 1px solid #dee2e6;">
              @endif
            @endforeach
          @endif
        </div>
      </div>
    </div>

  </div>
  <div class="invoice-footer">
    <p class="text-muted">© {{ date('Y') }} - ~SOMELEC-DISI~</p>
</div>
</body>
