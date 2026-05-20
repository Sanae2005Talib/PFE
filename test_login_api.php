<?php
// Script pour tester l'API login directement
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Login API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #10B981;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #10B981;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #0d9668;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            margin-bottom: 20px;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔐 Test Login API</h2>
        
        <div class="info">
            <strong>ℹ️ Info:</strong> Ce formulaire teste l'API login directement.<br>
            URL API: <code>http://app.solidarite.test/api/auth/login.php</code>
        </div>

        <form id="loginForm">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" id="email" name="email" value="admin@test.com" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" id="password" name="password" value="password123" required>
            </div>
            
            <button type="submit">Tester Login</button>
        </form>

        <div id="result"></div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const resultDiv = document.getElementById('result');
            
            resultDiv.innerHTML = '<div class="info">⏳ Test en cours...</div>';
            
            try {
                const response = await fetch('http://app.solidarite.test/api/auth/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="result success">
                            <h3>✅ Login Réussi!</h3>
                            <p><strong>Message:</strong> ${data.message}</p>
                            <p><strong>Nom:</strong> ${data.user.name}</p>
                            <p><strong>Email:</strong> ${data.user.email}</p>
                            <p><strong>Rôle:</strong> ${data.user.role}</p>
                            <p><strong>Token:</strong> ${data.token ? 'Généré' : 'Non généré'}</p>
                            <h4>Réponse complète:</h4>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="result error">
                            <h3>❌ Login Échoué</h3>
                            <p><strong>Message:</strong> ${data.message}</p>
                            <h4>Réponse complète:</h4>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="result error">
                        <h3>❌ Erreur</h3>
                        <p><strong>Message:</strong> ${error.message}</p>
                        <p>Vérifiez que l'API est accessible et que CORS est activé.</p>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>
