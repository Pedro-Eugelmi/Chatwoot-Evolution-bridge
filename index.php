<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conectar WhatsApp - API Não Oficial</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 h-screen flex flex-col items-center justify-center p-4">

    <div class="bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-xl max-w-md w-full text-center">
        <h1 class="text-xl font-semibold mb-2">Conectar WhatsApp</h1>
        <p class="text-sm text-slate-400 mb-6">Abra o WhatsApp no seu celular, vá em Aparelhos Conectados e escaneie o QR Code abaixo.</p>

        <div id="qr-container" class="bg-white p-4 rounded-xl inline-block mb-6 shadow-inner min-h-[256px] min-w-[256px] flex items-center justify-center">
            <span id="loading-text" class="text-slate-500 text-sm animate-pulse">Gerando QR Code...</span>
            <img id="qr-image" src="" alt="QR Code WhatsApp" class="hidden w-64 h-64 object-contain">
        </div>

        <div id="status-text" class="text-sm font-medium text-amber-400 mb-6">
            Aguardando leitura do QR Code...
        </div>

        <div class="flex gap-3 justify-center">
            <button onclick="carregarQRCode()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition-all">
                Atualizar QR Code
            </button>
        </div>
    </div>

    <script>
        async function carregarQRCode() {
            const loadingText = document.getElementById('loading-text');
            const qrImage = document.getElementById('qr-image');
            const statusText = document.getElementById('status-text');
        
            loadingText.classList.remove('hidden');
            qrImage.classList.add('hidden');
            
            // Mensagem solicitada
            loadingText.textContent = "Criando canal...";
            statusText.textContent = "Aguardando configuração...";
        
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const accountId = urlParams.get('account_id') || '';
                const company = urlParams.get('company') || 'org';
                const token = urlParams.get('token') || '';
        
                // Se o seu novo arquivo principal na raiz se chama index.php:
                const resposta = await fetch(`api.php?account_id=${accountId}&company=${company}&token=${token}`);
                
                const dados = await resposta.json();
        
                if (dados.base64) {
                    qrImage.src = dados.base64;
                    qrImage.classList.remove('hidden');
                    loadingText.classList.add('hidden');
                    statusText.textContent = "Aguardando leitura do QR Code...";
                } else {
                    statusText.textContent = dados.error || "Erro ao configurar canal. Tente novamente.";
                    loadingText.classList.add('hidden');
                }
            } catch (erro) {
                statusText.textContent = "Falha de conexão com o servidor.";
                loadingText.classList.add('hidden');
            }
        }

        // Executa assim que a página carrega
        carregarQRCode();
    </script>
</body>
</html>