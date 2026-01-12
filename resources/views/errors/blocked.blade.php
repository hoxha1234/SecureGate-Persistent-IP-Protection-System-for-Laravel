<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso Temporaneamente Bloccato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fc; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { max-width: 500px; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .icon-circle { width: 80px; height: 80px; background: #fff1f0; color: #e74a3b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px; }
    </style>
</head>
<body>
    <div class="container text-center">
        <div class="card error-card p-5 mx-auto">
            <div class="icon-circle"><i class="fas fa-user-shield"></i></div>
            <h2 class="text-dark font-weight-bold">Accesso Limitato</h2>
            <p class="text-muted">Abbiamo rilevato <strong>{{ $attempts }}</strong> tentativi di accesso falliti dal tuo indirizzo IP.</p>
            <div class="alert alert-danger">
                Per motivi di sicurezza, il tuo accesso è sospeso fino alle <strong>{{ $until }}</strong>.
            </div>
            <p class="small text-muted">Se ritieni che si tratti di un errore, contatta l'amministratore del sistema.</p>
            <a href="https://www.fatjonhoxha.it" class="btn btn-primary mt-3">Torna alla Home</a>
        </div>
    </div>
</body>
</html>