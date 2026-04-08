<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Pago | Llegamos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            min-height: 100vh;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 500px;
            width: 100%;
        }

        .card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 28px;
            color: #38bdf8;
            margin-bottom: 10px;
        }

        .header p {
            color: #cbd5e1;
            font-size: 14px;
        }

        .trip-summary {
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .trip-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .trip-info:last-child {
            margin-bottom: 0;
        }

        .trip-label {
            color: #cbd5e1;
        }

        .trip-value {
            color: #e2e8f0;
            font-weight: 600;
        }

        .divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 15px 0;
        }

        .total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
            color: #38bdf8;
        }

        .payment-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 14px;
            color: #cbd5e1;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .method {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            font-size: 13px;
        }

        .method:hover {
            border-color: rgba(56, 189, 248, 0.5);
            background: rgba(56, 189, 248, 0.05);
        }

        .method.active {
            border-color: #38bdf8;
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
        }

        .pay-button {
            width: 100%;
            padding: 15px;
            background: #38bdf8;
            color: #0f172a;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .pay-button:hover {
            background: #0ea5e9;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(56, 189, 248, 0.3);
        }

        .pay-button:disabled {
            background: #64748b;
            cursor: not-allowed;
        }

        .security-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            padding: 15px;
            background: rgba(34, 197, 94, 0.1);
            border-radius: 8px;
            font-size: 12px;
            color: #a7f3d0;
        }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #93c5fd;
            text-decoration: none;
            font-size: 14px;
        }

        .cancel-link:hover {
            color: #38bdf8;
        }

        .success-badge {
            display: inline-block;
            background: rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>💳 Procesar Pago</h1>
                <p>Completa tu pago para finalizar el viaje</p>
            </div>

            <!-- Trip Summary -->
            <div class="trip-summary">
                <div class="trip-info">
                    <span class="trip-label">Viaje ID</span>
                    <span class="trip-value">#{{ $trip->id }}</span>
                </div>
                <div class="trip-info">
                    <span class="trip-label">Origen</span>
                    <span class="trip-value" style="text-align: right; max-width: 50%;">{{ substr($trip->origin, 0, 20) }}...</span>
                </div>
                <div class="trip-info">
                    <span class="trip-label">Destino</span>
                    <span class="trip-value" style="text-align: right; max-width: 50%;">{{ substr($trip->destination, 0, 20) }}...</span>
                </div>
                @if($trip->distance_km)
                    <div class="trip-info">
                        <span class="trip-label">Distancia</span>
                        <span class="trip-value">{{ number_format($trip->distance_km, 2) }} km</span>
                    </div>
                @endif
                <div class="divider"></div>
                <div class="total">
                    <span>Total a pagar:</span>
                    <span>${{ number_format($trip->final_cost ?? $trip->estimated_cost, 2) }}</span>
                </div>
            </div>

            <!-- Payment Methods -->
            <form method="POST" action="{{ route('payment.process', $trip->id) }}" id="paymentForm">
                @csrf

                <div class="payment-section">
                    <div class="section-title">Método de Pago</div>
                    <div class="payment-methods">
                        <label class="method active" onclick="selectMethod('card', this)">
                            <input type="radio" name="payment_method" value="card" checked hidden>
                            <div>💳</div>
                            <div>Tarjeta</div>
                        </label>
                        <label class="method" onclick="selectMethod('cash', this)">
                            <input type="radio" name="payment_method" value="cash" hidden>
                            <div>💵</div>
                            <div>Efectivo</div>
                        </label>
                    </div>
                </div>

                <!-- Card Details (Initially visible) -->
                <div id="cardDetails" class="payment-section">
                    <div class="section-title">Información de la Tarjeta</div>
                    
                    <div style="margin-bottom: 15px;">
                        <input type="text" placeholder="Nombre del titular" 
                               class="input-field" required maxlength="50" style="width: 100%; padding: 10px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <input type="text" placeholder="Número de tarjeta (16 dígitos)" 
                               class="input-field" pattern="[0-9]{16}" maxlength="16" required style="width: 100%; padding: 10px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                        <input type="text" placeholder="MM/AA" class="input-field" pattern="[0-9]{2}/[0-9]{2}" 
                               maxlength="5" required style="padding: 10px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white;">
                        <input type="text" placeholder="CVV (3 dígitos)" class="input-field" pattern="[0-9]{3}" 
                               maxlength="3" required style="padding: 10px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: white;">
                    </div>
                </div>

                <!-- Cash Payment Info (Hidden) -->
                <div id="cashDetails" class="payment-section" style="display: none;">
                    <div style="background: rgba(251, 146, 60, 0.1); border: 1px solid rgba(251, 146, 60, 0.3); padding: 15px; border-radius: 8px; color: #fed7aa; font-size: 13px;">
                        <p>💵 Debes pagar ${{"$ " . number_format($trip->final_cost ?? $trip->estimated_cost, 2)}} en efectivo al conductor.</p>
                        <p style="margin-top: 10px;">Confirma el pago después de completar el viaje.</p>
                    </div>
                </div>

                <button type="submit" class="pay-button">✓ Confirmar Pago</button>
            </form>

            <!-- Security Info -->
            <div class="security-info">
                🔒 Tus datos están protegidos y cifrados
            </div>

            <!-- Cancel Button -->
            <a href="{{ route('trip.show', $trip->id) }}" class="cancel-link">← Cancelar</a>
        </div>
    </div>

    <script>
        function selectMethod(method, element) {
            // Remove active class from all methods
            document.querySelectorAll('.method').forEach(m => m.classList.remove('active'));
            
            // Add active class to clicked method
            element.classList.add('active');
            element.querySelector('input[type="radio"]').checked = true;

            // Show/hide card details
            if (method === 'card') {
                document.getElementById('cardDetails').style.display = 'block';
                document.getElementById('cashDetails').style.display = 'none';
            } else {
                document.getElementById('cardDetails').style.display = 'none';
                document.getElementById('cashDetails').style.display = 'block';
            }
        }

        // Auto-format card number input
        document.querySelectorAll('input[pattern="[0-9]{16}"]').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '');
                if (value.length > 0) {
                    value = value.match(/.{1,4}/g).join(' ');
                }
                e.target.value = value;
            });
        });

        // Auto-format MM/AA
        document.querySelectorAll('input[pattern="[0-9]{2}/[0-9]{2}"]').forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            });
        });

        // Handle form submission
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const method = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (method === 'card') {
                // For demo, just submit
                // In production, you'd integrate with Stripe
                this.submit();
            } else {
                // Cash payment - just submit
                this.submit();
            }
        });
    </script>
</body>
</html>
