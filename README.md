Chatwoot Evolution Bridge

Uma ferramenta de integração desenvolvida para conectar o Chatwoot à Evolution API de forma automatizada e segura, viabilizando o espelhamento de instâncias do WhatsApp (API não oficial) com gerenciamento dinâmico de caixas de entrada.

🛠️ O que este projeto faz?
Este repositório atua como o backend de integração, responsável por processar as solicitações vindas de painéis do Chatwoot integrados e automatizar o fluxo na Evolution API:

Autenticação Segura via HMAC (SHA-256):
Valida matematicamente as requisições recebidas através de um hash baseado no ID da conta e em uma chave secreta compartilhada (SECRET_KEY_HMAC), garantindo que apenas usuários autenticados no Chatwoot consigam acionar a criação de instâncias.

Integração com a Evolution API:

Criação de Instâncias: Cria automaticamente instâncias dedicadas e isoladas para cada conta/empresa (instance_{company}_{accountId}).

Configuração Automática: Vincula os webhooks, URLs e credenciais do Chatwoot diretamente na instância do WhatsApp.

Busca Dinâmica de Credenciais (ChatwootService):
Utiliza um serviço interno para consultar e obter tokens de acesso válidos da API do Chatwoot, assegurando a comunicação bidirecional sem falhas de autenticação.

Geração e Retorno do QR Code:
Solicita e retorna o QR Code em formato Base64 para exibição imediata ao usuário na interface de parelhamento.

⚙️ Arquitetura e Fluxo
Plaintext
[ Chatwoot do Cliente (Frontend c/ Injeção Visual) ] 
       │ (Envia account_id + hash HMAC)
       ▼
[ Este Projeto: Backend de Integração (PHP) ] 
       │ (Valida HMAC, busca token no Chatwoot e configura a Evolution API)
       ▼
[ Evolution API ] 
       │ (Retorna o QR Code Base64)
       ▼
[ Exibição do QR Code para o Usuário ]
🔒 Segurança
Validação por HMAC: Protege o endpoint contra acessos indesejados ou chamadas não autorizadas.

Isolamento de Contas: Separação estrita de instâncias por ID de conta, evitando sobreposições ou vazamentos de dados entre diferentes empresas.
