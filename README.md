# 🚀 Chatwoot Evolution Bridge

> Uma ferramenta de integração backend desenvolvida para conectar o Chatwoot à Evolution API de forma automatizada, segura e escalável, viabilizando o espelhamento de instâncias do WhatsApp com gerenciamento dinâmico de caixas de entrada.

---

## 💡 Por que este projeto foi criado?

No ecossistema atual de atendimento omnichannel, muitas empresas e desenvolvedores enfrentam uma barreira frustrante: **conectar plataformas de CRM de atendimento (como o Chatwoot) a APIs de WhatsApp não oficiais (como a Evolution API) de forma segura, automatizada e escalável sem expor brechas de segurança.**

Este projeto nasceu para resolver um problema real de engenharia e produto:
* **Eliminar processos manuais:** Automatizar a criação e o mapeamento de instâncias e caixas de entrada sem precisar de intervenção técnica manual a cada novo cliente *onboarded*.
* **Blindar contra vulnerabilidades:** Garantir que o painel do cliente consiga despoletar a criação de conexões de forma estritamente autenticada (utilizando criptografia HMAC-SHA-256), evitando acessos indevidos ou chamadas maliciosas à API.
* **Escalabilidade Multi-Tenant:** Permitir que agências e empresas gerenciem múltiplos clientes de forma isolada e segura (`instance_{company}_{accountId}`), servindo como a fundação perfeita para operações em modelo *White-Label*.

---

## 🛠️ O que este projeto faz?

Este repositório atua como um microsserviço de integração backend, responsável por processar solicitações vindas de painéis do Chatwoot e automatizar todo o ciclo de vida das instâncias na Evolution API:

* **Autenticação Segura via HMAC (SHA-256):** Valida matematicamente as requisições recebidas através de um *hash* baseado no ID da conta e numa chave secreta compartilhada (`SECRET_KEY_HMAC`), garantindo que apenas usuários autenticados no Chatwoot consigam acionar a criação de instâncias.
* **Integração Nativa com la Evolution API:**
  * **Criação de Instâncias:** Cria automaticamente instâncias dedicadas e isoladas para cada conta/empresa (`instance_{company}_{accountId}`).
  * **Configuração Automática:** Vincula webhooks, URLs e credenciais do Chatwoot diretamente na instância do WhatsApp.
* **Busca Dinâmica de Credenciais (`ChatwootService`):** Consulta e obtém tokens de acesso válidos de forma dinâmica, assegurando a comunicação bidirecional sem falhas de autenticação.
* **Geração e Retorno de QR Code:** Solicita e retorna o QR Code em formato Base64 para exibição imediata ao usuário na interface de parelhamento.

---

## ⚙️ Arquitetura e Fluxo de Execução

```text
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
